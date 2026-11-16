<?php

namespace App\Services;

use App\Models\Lecturer;
use App\Models\Student;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class ProfilePhotoService
{
    public const MAX_IMAGE_BYTES = 5 * 1024 * 1024; // 5 MB per image

    public const TARGET_SIZE = 256;

    private const ALLOWED_MIMES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function disk(): string
    {
        return (string) config('filesystems.avatar_disk', 'local');
    }

    public function storeUploadedFile(UploadedFile $file, string $type, string|int $identifier, ?string $oldPath = null): string
    {
        $bytes = file_get_contents($file->getRealPath());
        $fallbackExt = strtolower($file->getClientOriginalExtension() ?: ($file->extension() ?: 'jpg'));

        return $this->storeBytes($bytes, $fallbackExt, $type, $identifier, $oldPath);
    }

    public function storeBytes(string $bytes, string $fallbackExtension, string $type, string|int $identifier, ?string $oldPath = null): string
    {
        ['bytes' => $encoded, 'extension' => $extension] = $this->compressAndEncode($bytes, $fallbackExtension);

        $safeId = preg_replace('/[^A-Za-z0-9_-]/', '_', (string) $identifier);
        $path = "avatars/{$type}/{$safeId}_".Str::random(8).".{$extension}";

        Storage::disk($this->disk())->put($path, $encoded);

        if ($oldPath && $oldPath !== $path) {
            $this->delete($oldPath);
        }

        return $path;
    }

    public function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        $disk = Storage::disk($this->disk());
        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }

    public function response(string $path): StreamedResponse
    {
        $disk = Storage::disk($this->disk());
        abort_unless($disk->exists($path), 404);

        return $disk->response($path, null, [
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }

    /**
     * Parse and validate a ZIP archive for bulk student or lecturer photo uploads.
     *
     * @return array{
     *     total: int,
     *     valid: int,
     *     skipped: int,
     *     rows: list<array{entry: string, filename: string, code: string, matched_name: ?string, action: string, status: string}>
     * }
     */
    public function inspectZip(string $zipPath, string $type): array
    {
        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            abort(422, 'Could not open the uploaded ZIP file.');
        }

        $lookup = $this->buildLookup($type);
        $seen = [];
        $rows = [];
        $valid = 0;
        $skipped = 0;

        try {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entry = $zip->getNameIndex($i);
                if ($this->shouldIgnoreZipEntry($entry)) {
                    continue;
                }

                $filename = basename($entry);
                $code = trim(pathinfo($filename, PATHINFO_FILENAME));
                $key = strtolower($code);

                if ($code === '' || ! isset($lookup[$key])) {
                    $rows[] = [
                        'entry' => $entry,
                        'filename' => $filename,
                        'code' => $code ?: '-',
                        'matched_name' => null,
                        'action' => 'Skipped',
                        'status' => $type === 'students' ? 'No matching Student ID' : 'No matching Lecturer Code',
                    ];
                    $skipped++;

                    continue;
                }

                if (isset($seen[$key])) {
                    $rows[] = [
                        'entry' => $entry,
                        'filename' => $filename,
                        'code' => $code,
                        'matched_name' => $lookup[$key]['name'],
                        'action' => 'Skipped',
                        'status' => 'Duplicate file for the same ID in ZIP',
                    ];
                    $skipped++;

                    continue;
                }

                $stat = $zip->statIndex($i);
                if (($stat['size'] ?? 0) > self::MAX_IMAGE_BYTES) {
                    $rows[] = [
                        'entry' => $entry,
                        'filename' => $filename,
                        'code' => $code,
                        'matched_name' => $lookup[$key]['name'],
                        'action' => 'Skipped',
                        'status' => 'Image exceeds 5 MB limit',
                    ];
                    $skipped++;

                    continue;
                }

                $bytes = $zip->getFromIndex($i);
                $info = $bytes !== false ? @getimagesizefromstring($bytes) : false;
                $mime = $info['mime'] ?? null;

                if (! $info || ! isset(self::ALLOWED_MIMES[$mime])) {
                    $rows[] = [
                        'entry' => $entry,
                        'filename' => $filename,
                        'code' => $code,
                        'matched_name' => $lookup[$key]['name'],
                        'action' => 'Skipped',
                        'status' => 'Unsupported or invalid image (use JPG, PNG, or WebP)',
                    ];
                    $skipped++;

                    continue;
                }

                $seen[$key] = true;
                $action = $lookup[$key]['has_photo'] ? 'Replace' : 'New';
                $rows[] = [
                    'entry' => $entry,
                    'filename' => $filename,
                    'code' => $code,
                    'matched_name' => $lookup[$key]['name'],
                    'action' => $action,
                    'status' => $action === 'Replace' ? 'Ready — replaces existing photo' : 'Ready',
                ];
                $valid++;
            }
        } finally {
            $zip->close();
        }

        return [
            'total' => $valid + $skipped,
            'valid' => $valid,
            'skipped' => $skipped,
            'rows' => $rows,
        ];
    }

    /**
     * Commit all valid entries from a previously inspected ZIP file.
     */
    public function commitZip(string $zipPath, string $type, AuditLogger $auditLogger): int
    {
        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            abort(422, 'Could not reopen the uploaded ZIP file.');
        }

        $inspection = $this->inspectZip($zipPath, $type);
        $validRows = array_filter($inspection['rows'], fn (array $row) => $row['action'] !== 'Skipped');
        $updatedCount = 0;

        try {
            foreach ($validRows as $row) {
                $bytes = $zip->getFromName($row['entry']);
                if ($bytes === false) {
                    continue;
                }

                $info = @getimagesizefromstring($bytes);
                $mime = $info['mime'] ?? '';
                if (! isset(self::ALLOWED_MIMES[$mime])) {
                    continue;
                }

                $model = $type === 'students'
                    ? Student::whereRaw('LOWER(nim) = ?', [strtolower($row['code'])])->first()
                    : Lecturer::whereRaw('LOWER(lecturer_code) = ?', [strtolower($row['code'])])->first();

                if (! $model) {
                    continue;
                }

                $oldPath = $model->photo_path;
                $identifier = $type === 'students' ? $model->nim : $model->lecturer_code;
                $newPath = $this->storeBytes($bytes, self::ALLOWED_MIMES[$mime], $type, $identifier, $oldPath);

                $model->forceFill(['photo_path' => $newPath])->save();
                $auditLogger->record(
                    'UPDATE_PHOTO',
                    $type === 'students' ? 'Student' : 'Lecturer',
                    $model->id,
                    ['photo_path' => $oldPath],
                    ['photo_path' => $newPath]
                );
                $updatedCount++;
            }
        } finally {
            $zip->close();
        }

        return $updatedCount;
    }

    /**
     * @return array{bytes: string, extension: string}
     */
    private function compressAndEncode(string $bytes, string $fallbackExtension): array
    {
        $normalizedExt = match (strtolower($fallbackExtension)) {
            'jpeg', 'jpg' => 'jpg',
            'png' => 'png',
            'webp' => 'webp',
            default => 'jpg',
        };

        if (! function_exists('imagecreatefromstring')) {
            return ['bytes' => $bytes, 'extension' => $normalizedExt];
        }

        $source = @imagecreatefromstring($bytes);
        if (! $source) {
            return ['bytes' => $bytes, 'extension' => $normalizedExt];
        }

        $srcWidth = imagesx($source);
        $srcHeight = imagesy($source);
        $cropSize = min($srcWidth, $srcHeight);
        $srcX = (int) floor(($srcWidth - $cropSize) / 2);
        $srcY = (int) floor(($srcHeight - $cropSize) / 2);

        $target = imagecreatetruecolor(self::TARGET_SIZE, self::TARGET_SIZE);
        imagealphablending($target, false);
        imagesavealpha($target, true);
        $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
        imagefill($target, 0, 0, $transparent);

        imagecopyresampled(
            $target,
            $source,
            0,
            0,
            $srcX,
            $srcY,
            self::TARGET_SIZE,
            self::TARGET_SIZE,
            $cropSize,
            $cropSize
        );

        ob_start();
        if (function_exists('imagewebp')) {
            imagewebp($target, null, 82);
            $encoded = ob_get_clean();
            $outExt = 'webp';
        } else {
            imagejpeg($target, null, 82);
            $encoded = ob_get_clean();
            $outExt = 'jpg';
        }

        imagedestroy($source);
        imagedestroy($target);

        if (! is_string($encoded) || $encoded === '') {
            return ['bytes' => $bytes, 'extension' => $normalizedExt];
        }

        return ['bytes' => $encoded, 'extension' => $outExt];
    }

    /**
     * @return array<string, array{id: int, name: string, has_photo: bool}>
     */
    private function buildLookup(string $type): array
    {
        $map = [];

        if ($type === 'students') {
            Student::query()->select(['id', 'nim', 'name', 'photo_path'])->each(function (Student $student) use (&$map) {
                $map[strtolower($student->nim)] = [
                    'id' => $student->id,
                    'name' => "{$student->nim} — {$student->name}",
                    'has_photo' => filled($student->photo_path),
                ];
            });
        } else {
            Lecturer::query()->select(['id', 'lecturer_code', 'name', 'photo_path'])->each(function (Lecturer $lecturer) use (&$map) {
                $map[strtolower($lecturer->lecturer_code)] = [
                    'id' => $lecturer->id,
                    'name' => "{$lecturer->lecturer_code} — {$lecturer->name}",
                    'has_photo' => filled($lecturer->photo_path),
                ];
            });
        }

        return $map;
    }

    private function shouldIgnoreZipEntry(string $entry): bool
    {
        if (str_ends_with($entry, '/')) {
            return true;
        }

        if (str_contains($entry, '__MACOSX/')) {
            return true;
        }

        $base = basename($entry);

        return $base === '' || str_starts_with($base, '.') || strcasecmp($base, 'Thumbs.db') === 0;
    }
}
