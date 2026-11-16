<?php

namespace App\Http\Controllers;

use App\Models\Lecturer;
use App\Models\Student;
use App\Services\AuditLogger;
use App\Services\ProfilePhotoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfilePhotoController extends Controller
{
    public function __construct(private ProfilePhotoService $photos) {}

    public function showStudent(Student $student): StreamedResponse
    {
        abort_unless($student->photo_path, 404);

        return $this->photos->response($student->photo_path);
    }

    public function updateStudent(Request $request, Student $student, AuditLogger $auditLogger): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $oldPath = $student->photo_path;
        $newPath = $this->photos->storeUploadedFile($request->file('photo'), 'students', $student->nim, $oldPath);

        $student->forceFill(['photo_path' => $newPath])->save();
        $auditLogger->record('UPDATE_PHOTO', 'Student', $student->id, ['photo_path' => $oldPath], ['photo_path' => $newPath]);

        return back()->with('success', 'Profile photo updated successfully.');
    }

    public function destroyStudent(Student $student, AuditLogger $auditLogger): RedirectResponse
    {
        if ($student->photo_path) {
            $oldPath = $student->photo_path;
            $this->photos->delete($oldPath);
            $student->forceFill(['photo_path' => null])->save();
            $auditLogger->record('REMOVE_PHOTO', 'Student', $student->id, ['photo_path' => $oldPath], ['photo_path' => null]);
        }

        return back()->with('success', 'Profile photo removed.');
    }

    public function showLecturer(Lecturer $lecturer): StreamedResponse
    {
        abort_unless($lecturer->photo_path, 404);

        return $this->photos->response($lecturer->photo_path);
    }

    public function updateLecturer(Request $request, Lecturer $lecturer, AuditLogger $auditLogger): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $oldPath = $lecturer->photo_path;
        $newPath = $this->photos->storeUploadedFile($request->file('photo'), 'lecturers', $lecturer->lecturer_code, $oldPath);

        $lecturer->forceFill(['photo_path' => $newPath])->save();
        $auditLogger->record('UPDATE_PHOTO', 'Lecturer', $lecturer->id, ['photo_path' => $oldPath], ['photo_path' => $newPath]);

        return back()->with('success', 'Profile photo updated successfully.');
    }

    public function destroyLecturer(Lecturer $lecturer, AuditLogger $auditLogger): RedirectResponse
    {
        if ($lecturer->photo_path) {
            $oldPath = $lecturer->photo_path;
            $this->photos->delete($oldPath);
            $lecturer->forceFill(['photo_path' => null])->save();
            $auditLogger->record('REMOVE_PHOTO', 'Lecturer', $lecturer->id, ['photo_path' => $oldPath], ['photo_path' => null]);
        }

        return back()->with('success', 'Profile photo removed.');
    }

    public function previewBulk(Request $request, string $type): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:zip', 'max:51200'],
        ]);

        $token = Str::uuid()->toString();
        $tempPath = "tmp/bulk-photos/{$token}.zip";

        Storage::disk('local')->putFileAs('tmp/bulk-photos', $request->file('file'), "{$token}.zip");
        $fullPath = Storage::disk('local')->path($tempPath);

        $result = $this->photos->inspectZip($fullPath, $type);

        Cache::put($this->cacheKey($type, $token), $tempPath, now()->addMinutes(30));

        return response()->json([
            'token' => $token,
            'total' => $result['total'],
            'valid' => $result['valid'],
            'skipped' => $result['skipped'],
            'rows' => array_slice($result['rows'], 0, 200),
        ]);
    }

    public function commitBulk(Request $request, string $type, AuditLogger $auditLogger): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
        ]);

        $cacheKey = $this->cacheKey($type, $validated['token']);
        $tempPath = Cache::pull($cacheKey);

        if (! $tempPath || ! Storage::disk('local')->exists($tempPath)) {
            return response()->json(['message' => 'Upload session expired. Please upload the ZIP file again.'], 422);
        }

        $fullPath = Storage::disk('local')->path($tempPath);

        try {
            $count = $this->photos->commitZip($fullPath, $type, $auditLogger);
        } finally {
            Storage::disk('local')->delete($tempPath);
        }

        session()->flash('success', "{$count} profile photo(s) updated.");

        return response()->json(['updated' => $count]);
    }

    private function cacheKey(string $type, string $token): string
    {
        return "bulk-photos:{$type}:{$token}";
    }
}
