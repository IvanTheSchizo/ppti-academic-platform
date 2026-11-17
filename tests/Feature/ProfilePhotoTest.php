<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Batch;
use App\Models\ClassGroup;
use App\Models\Lecturer;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class ProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    private function tinyPngBytes(): string
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
    }

    private function createFixtures(): array
    {
        $admin = Admin::create([
            'username' => 'admin_photo',
            'password' => Hash::make('Password1!'),
        ]);

        $batch = Batch::create(['batch_name' => 'PPTI 24']);
        $classGroup = ClassGroup::create(['batch_id' => $batch->id, 'class_code' => 'PPTI 24A']);

        $student = Student::create([
            'nim' => '2700000401',
            'name' => 'Photo Student',
            'class_id' => $classGroup->id,
            'status' => 'Active',
            'cumulative_gpa' => 3.50,
        ]);

        $lecturer = Lecturer::create([
            'lecturer_code' => 'D4001',
            'name' => 'Dr. Photo',
            'status' => 'Active',
        ]);

        return compact('admin', 'student', 'lecturer');
    }

    public function test_individual_student_and_lecturer_photo_upload_serve_and_remove(): void
    {
        Storage::fake('local');
        config(['filesystems.avatar_disk' => 'local']);

        $data = $this->createFixtures();
        $this->actingAs($data['admin']);

        $file = UploadedFile::fake()->createWithContent('avatar.png', $this->tinyPngBytes());

        $this->post(route('students.photo.update', $data['student']->id), ['photo' => $file])
            ->assertRedirect();

        $student = $data['student']->fresh();
        $this->assertNotNull($student->photo_path);
        Storage::disk('local')->assertExists($student->photo_path);
        $firstUrl = $student->photoUrl();

        $replacementFile = UploadedFile::fake()->createWithContent('avatar2.png', $this->tinyPngBytes());
        $this->post(route('students.photo.update', $student->id), ['photo' => $replacementFile])
            ->assertRedirect();
        $student = $student->fresh();
        $this->assertNotSame($firstUrl, $student->photoUrl());

        $this->get(route('students.photo', $student->id))->assertOk();
        $this->assertDatabaseHas('audit_logs', [
            'action_type' => 'UPDATE_PHOTO',
            'target_entity' => 'Student',
            'target_id' => $student->id,
        ]);

        $oldPath = $student->photo_path;
        $this->delete(route('students.photo.destroy', $student->id))->assertRedirect();
        $this->assertNull($student->fresh()->photo_path);
        Storage::disk('local')->assertMissing($oldPath);
        $this->assertDatabaseHas('audit_logs', [
            'action_type' => 'REMOVE_PHOTO',
            'target_entity' => 'Student',
            'target_id' => $student->id,
        ]);

        $lecturerFile = UploadedFile::fake()->createWithContent('lecturer.png', $this->tinyPngBytes());
        $this->post(route('lecturers.photo.update', $data['lecturer']->id), ['photo' => $lecturerFile])
            ->assertRedirect();

        $lecturer = $data['lecturer']->fresh();
        $this->assertNotNull($lecturer->photo_path);
        Storage::disk('local')->assertExists($lecturer->photo_path);
        $this->get(route('lecturers.photo', $lecturer->id))->assertOk();

        $this->delete(route('lecturers.photo.destroy', $lecturer->id))->assertRedirect();
        $this->assertNull($lecturer->fresh()->photo_path);
    }

    public function test_bulk_zip_preview_and_commit_matches_student_ids_and_lecturer_codes(): void
    {
        Storage::fake('local');
        config(['filesystems.avatar_disk' => 'local']);

        $data = $this->createFixtures();
        $this->actingAs($data['admin']);

        $zipFile = tempnam(sys_get_temp_dir(), 'photos_').'.zip';
        $zip = new ZipArchive;
        $zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('photos/2700000401.png', $this->tinyPngBytes());
        $zip->addFromString('unknown_999.png', $this->tinyPngBytes());
        $zip->addFromString('__MACOSX/._2700000401.png', 'junk');
        $zip->close();

        $uploadedZip = new UploadedFile($zipFile, 'students.zip', 'application/zip', null, true);

        $preview = $this->postJson(route('students.photos.preview'), ['file' => $uploadedZip]);
        $preview->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('valid', 1)
            ->assertJsonPath('skipped', 1);

        $token = $preview->json('token');

        $this->postJson(route('students.photos.commit'), ['token' => $token])
            ->assertOk()
            ->assertJsonPath('updated', 1);

        $student = $data['student']->fresh();
        $this->assertNotNull($student->photo_path);
        Storage::disk('local')->assertExists($student->photo_path);
        $firstUrl = $student->photoUrl();

        $replacementFile = UploadedFile::fake()->createWithContent('avatar2.png', $this->tinyPngBytes());
        $this->post(route('students.photo.update', $student->id), ['photo' => $replacementFile])
            ->assertRedirect();
        $student = $student->fresh();
        $this->assertNotSame($firstUrl, $student->photoUrl());

        @unlink($zipFile);

        $lecturerZipFile = tempnam(sys_get_temp_dir(), 'lphotos_').'.zip';
        $lzip = new ZipArchive;
        $lzip->open($lecturerZipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $lzip->addFromString('d4001.png', $this->tinyPngBytes());
        $lzip->close();

        $uploadedLecturerZip = new UploadedFile($lecturerZipFile, 'lecturers.zip', 'application/zip', null, true);

        $lPreview = $this->postJson(route('lecturers.photos.preview'), ['file' => $uploadedLecturerZip]);
        $lPreview->assertOk()->assertJsonPath('valid', 1);

        $this->postJson(route('lecturers.photos.commit'), ['token' => $lPreview->json('token')])
            ->assertOk()
            ->assertJsonPath('updated', 1);

        $this->assertNotNull($data['lecturer']->fresh()->photo_path);

        @unlink($lecturerZipFile);
    }

    public function test_admin_can_update_display_name_and_profile_photo(): void
    {
        Storage::fake('local');
        config(['filesystems.avatar_disk' => 'local']);

        $data = $this->createFixtures();
        $this->actingAs($data['admin']);

        $this->put(route('account.display-name.update'), ['display_name' => 'Ivan Admin'])
            ->assertRedirect();

        $admin = $data['admin']->fresh();
        $this->assertSame('Ivan Admin', $admin->display_name);
        $this->assertSame('Ivan Admin', $admin->displayName());
        $this->assertDatabaseHas('audit_logs', [
            'action_type' => 'UPDATE',
            'target_entity' => 'Admin',
            'target_id' => $admin->id,
        ]);

        $file = UploadedFile::fake()->createWithContent('admin.png', $this->tinyPngBytes());
        $this->post(route('account.photo.update'), ['photo' => $file])->assertRedirect();

        $admin = $admin->fresh();
        $this->assertNotNull($admin->photo_path);
        Storage::disk('local')->assertExists($admin->photo_path);
        $this->get(route('account.photo'))->assertOk();
        $this->assertDatabaseHas('audit_logs', [
            'action_type' => 'UPDATE_PHOTO',
            'target_entity' => 'Admin',
            'target_id' => $admin->id,
        ]);

        $oldPath = $admin->photo_path;
        $this->delete(route('account.photo.destroy'))->assertRedirect();
        $this->assertNull($admin->fresh()->photo_path);
        Storage::disk('local')->assertMissing($oldPath);
        $this->assertDatabaseHas('audit_logs', [
            'action_type' => 'REMOVE_PHOTO',
            'target_entity' => 'Admin',
            'target_id' => $admin->id,
        ]);
    }
}
