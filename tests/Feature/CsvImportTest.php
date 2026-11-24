<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Lecturer;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CsvImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_csv_import_works_when_default_filesystem_disk_is_s3(): void
    {
        Storage::fake('s3');
        config(['filesystems.default' => 's3']);

        $admin = Admin::create([
            'username' => 'admin_csv',
            'password' => Hash::make('Password1!'),
        ]);
        $this->actingAs($admin);

        $csv = implode("\n", [
            'nim,name,class_code,batch,status,gender,email,phone_number,date_of_birth,cumulative_gpa',
            '2602610001,Wulan Anggraini,11-A,PPTI18,Active,Male,wulan.anggraini.1@example.com,+628124866262,2001-12-07,2.43',
            '2602610069,Vania Permata,13-A,PPTI20,Graduated,Female,vania.permata.69@example.com,+628129048364,2005-06-19,',
        ]);

        $file = UploadedFile::fake()->createWithContent('students.csv', $csv);

        $preview = $this->postJson(route('input-data.import.preview', 'student'), ['file' => $file]);
        $preview->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('valid', 2)
            ->assertJsonPath('invalid', 0);

        $token = $preview->json('token');

        $this->postJson(route('input-data.import.commit', 'student'), ['token' => $token])
            ->assertOk()
            ->assertJsonPath('imported', 2)
            ->assertJsonPath('skipped', 0);

        $this->assertDatabaseHas('students', ['nim' => '2602610001', 'name' => 'Wulan Anggraini', 'cumulative_gpa' => 2.43]);
        $this->assertDatabaseHas('students', ['nim' => '2602610069', 'name' => 'Vania Permata', 'cumulative_gpa' => 0]);
        $this->assertSame(2, Student::count());

        // Re-importing updates existing students (and preserves GPA when blank).
        $updateCsv = implode("\n", [
            'nim,name,class_code,batch,status,gender,email,phone_number,date_of_birth,cumulative_gpa',
            '2602610001,Wulan Anggraini,11-A,PPTI18,Active,Male,wulan.anggraini.1@example.com,+628124866262,2001-12-07,',
            '2602610069,Vania Permata,13-A,PPTI20,Graduated,Female,vania.permata.69@example.com,+628129048364,2005-06-19,3.75',
        ]);

        $updateFile = UploadedFile::fake()->createWithContent('students_update.csv', $updateCsv);
        $updatePreview = $this->postJson(route('input-data.import.preview', 'student'), ['file' => $updateFile]);
        $updatePreview->assertOk()->assertJsonPath('valid', 2)->assertJsonPath('invalid', 0);

        $this->postJson(route('input-data.import.commit', 'student'), ['token' => $updatePreview->json('token')])
            ->assertOk()
            ->assertJsonPath('imported', 2);

        $this->assertSame(2, Student::count());
        $this->assertDatabaseHas('students', ['nim' => '2602610001', 'cumulative_gpa' => 2.43]);
        $this->assertDatabaseHas('students', ['nim' => '2602610069', 'cumulative_gpa' => 3.75]);
        $this->assertDatabaseHas('audit_logs', ['action_type' => 'UPDATE', 'target_entity' => 'Student']);
    }

    public function test_lecturer_csv_import_updates_existing_lecturers_and_retains_blank_fields(): void
    {
        $admin = Admin::create([
            'username' => 'admin_lecturer_csv',
            'password' => Hash::make('Password1!'),
        ]);
        $this->actingAs($admin);

        $initialCsv = implode("\n", [
            'lecturer_code,name,jja,latest_education,status,gender,email_binus_edu,email_binus_ac_id,phone_number',
            'D1001,Dr. Alpha,Lektor,S3,Active,Male,wrong.alpha@binus.edu,wrong.alpha@binus.ac.id,+628111111111',
            'D1002,Dr. Beta,Asisten Ahli,S2,Inactive,Female,wrong.beta@binus.edu,wrong.beta@binus.ac.id,+628222222222',
        ]);

        $initialFile = UploadedFile::fake()->createWithContent('lecturers.csv', $initialCsv);
        $initialPreview = $this->postJson(route('input-data.import.preview', 'lecturer'), ['file' => $initialFile]);
        $initialPreview->assertOk()->assertJsonPath('valid', 2)->assertJsonPath('invalid', 0);

        $this->postJson(route('input-data.import.commit', 'lecturer'), ['token' => $initialPreview->json('token')])
            ->assertOk()
            ->assertJsonPath('imported', 2)
            ->assertJsonPath('skipped', 0);

        $this->assertSame(2, Lecturer::count());

        $updateCsv = implode("\n", [
            'lecturer_code,name,jja,latest_education,status,gender,email_binus_edu,email_binus_ac_id,phone_number',
            'D1001,,,,,,alpha@binus.edu,alpha@binus.ac.id,',
            'D1002,Dr. Beta Updated,,,,,beta@binus.edu,beta@binus.ac.id,',
        ]);

        $updateFile = UploadedFile::fake()->createWithContent('lecturers_update.csv', $updateCsv);
        $updatePreview = $this->postJson(route('input-data.import.preview', 'lecturer'), ['file' => $updateFile]);
        $updatePreview->assertOk()
            ->assertJsonPath('valid', 2)
            ->assertJsonPath('invalid', 0)
            ->assertJsonPath('rows.0.notices.0', 'Existing lecturer "D1001" will be updated.')
            ->assertJsonPath('rows.1.notices.0', 'Existing lecturer "D1002" will be updated.');

        $this->postJson(route('input-data.import.commit', 'lecturer'), ['token' => $updatePreview->json('token')])
            ->assertOk()
            ->assertJsonPath('imported', 2)
            ->assertJsonPath('skipped', 0);

        $this->assertSame(2, Lecturer::count());

        $this->assertDatabaseHas('lecturers', [
            'lecturer_code' => 'D1001',
            'name' => 'Dr. Alpha',
            'jja' => 'Lektor',
            'latest_education' => 'S3',
            'status' => 'Active',
            'gender' => 'Male',
            'email_binus_edu' => 'alpha@binus.edu',
            'email_binus_ac_id' => 'alpha@binus.ac.id',
            'phone_number' => '+628111111111',
        ]);

        $this->assertDatabaseHas('lecturers', [
            'lecturer_code' => 'D1002',
            'name' => 'Dr. Beta Updated',
            'jja' => 'Asisten Ahli',
            'latest_education' => 'S2',
            'status' => 'Inactive',
            'gender' => 'Female',
            'email_binus_edu' => 'beta@binus.edu',
            'email_binus_ac_id' => 'beta@binus.ac.id',
            'phone_number' => '+628222222222',
        ]);

        $this->assertDatabaseHas('audit_logs', ['action_type' => 'UPDATE', 'target_entity' => 'Lecturer']);
    }

    public function test_lecturer_csv_import_rejects_duplicate_emails_within_file_or_other_lecturers(): void
    {
        $admin = Admin::create([
            'username' => 'admin_lecturer_dup',
            'password' => Hash::make('Password1!'),
        ]);
        $this->actingAs($admin);

        Lecturer::create([
            'lecturer_code' => 'D2001',
            'name' => 'Dr. First',
            'email_binus_edu' => 'first@binus.edu',
            'email_binus_ac_id' => 'first@binus.ac.id',
            'status' => 'Active',
        ]);
        Lecturer::create([
            'lecturer_code' => 'D2002',
            'name' => 'Dr. Second',
            'email_binus_edu' => 'second@binus.edu',
            'email_binus_ac_id' => 'second@binus.ac.id',
            'status' => 'Active',
        ]);

        // Two rows in the same CSV sharing email_binus_edu
        $sameFileCsv = implode("\n", [
            'lecturer_code,name,jja,latest_education,status,gender,email_binus_edu,email_binus_ac_id,phone_number',
            'D2003,Dr. Third,,,,,shared@binus.edu,third@binus.ac.id,',
            'D2004,Dr. Fourth,,,,,shared@binus.edu,fourth@binus.ac.id,',
        ]);

        $sameFile = UploadedFile::fake()->createWithContent('same_file_dup.csv', $sameFileCsv);
        $samePreview = $this->postJson(route('input-data.import.preview', 'lecturer'), ['file' => $sameFile]);
        $samePreview->assertOk()
            ->assertJsonPath('valid', 1)
            ->assertJsonPath('invalid', 1);

        // Updating D2001 with D2002's existing email_binus_edu
        $crossLecturerCsv = implode("\n", [
            'lecturer_code,name,jja,latest_education,status,gender,email_binus_edu,email_binus_ac_id,phone_number',
            'D2001,,,,,,second@binus.edu,,',
        ]);

        $crossFile = UploadedFile::fake()->createWithContent('cross_lecturer_dup.csv', $crossLecturerCsv);
        $crossPreview = $this->postJson(route('input-data.import.preview', 'lecturer'), ['file' => $crossFile]);
        $crossPreview->assertOk()
            ->assertJsonPath('valid', 0)
            ->assertJsonPath('invalid', 1);
    }

    public function test_lecturer_csv_import_handles_multi_batch_and_job_start_date_and_retains_blank_values(): void
    {
        $admin = Admin::create([
            'username' => 'admin_lecturer_batch_csv',
            'password' => Hash::make('Password1!'),
        ]);
        $this->actingAs($admin);

        $csv = implode("\n", [
            'lecturer_code,name,batch,job_start_date,jja,latest_education,status,gender,email_binus_edu,email_binus_ac_id,phone_number',
            'D3001,Dr. Multi,PPTI18;PPTI19,2022-08-15,Lektor,S3,Active,Male,multi@binus.edu,multi@binus.ac.id,+628333333333',
        ]);

        $file = UploadedFile::fake()->createWithContent('lecturers_batch.csv', $csv);
        $preview = $this->postJson(route('input-data.import.preview', 'lecturer'), ['file' => $file]);
        $preview->assertOk()->assertJsonPath('valid', 1)->assertJsonPath('invalid', 0);

        $this->postJson(route('input-data.import.commit', 'lecturer'), ['token' => $preview->json('token')])
            ->assertOk()
            ->assertJsonPath('imported', 1);

        $lecturer = Lecturer::where('lecturer_code', 'D3001')->with('batches')->firstOrFail();
        $this->assertSame(['PPTI18', 'PPTI19'], $lecturer->sortedBatchNames());
        $this->assertSame('2022-08-15', $lecturer->job_start_date?->toDateString());

        // Re-import with blank batch and job_start_date retains existing values
        $reimportCsv = implode("\n", [
            'lecturer_code,name,batch,job_start_date,jja,latest_education,status,gender,email_binus_edu,email_binus_ac_id,phone_number',
            'D3001,Dr. Multi Updated,,,,,,,,multi2@binus.ac.id,',
        ]);

        $reimportFile = UploadedFile::fake()->createWithContent('lecturers_reimport.csv', $reimportCsv);
        $reimportPreview = $this->postJson(route('input-data.import.preview', 'lecturer'), ['file' => $reimportFile]);
        $reimportPreview->assertOk()->assertJsonPath('valid', 1)->assertJsonPath('invalid', 0);

        $this->postJson(route('input-data.import.commit', 'lecturer'), ['token' => $reimportPreview->json('token')])
            ->assertOk()
            ->assertJsonPath('imported', 1);

        $lecturer->refresh()->load('batches');
        $this->assertSame('Dr. Multi Updated', $lecturer->name);
        $this->assertSame(['PPTI18', 'PPTI19'], $lecturer->sortedBatchNames());
        $this->assertSame('2022-08-15', $lecturer->job_start_date?->toDateString());
        $this->assertSame('multi2@binus.ac.id', $lecturer->email_binus_ac_id);
    }
}
