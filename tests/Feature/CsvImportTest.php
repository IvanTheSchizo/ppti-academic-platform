<?php

namespace Tests\Feature;

use App\Models\Admin;
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
    }
}
