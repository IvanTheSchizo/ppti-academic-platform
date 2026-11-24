<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Batch;
use App\Models\ClassGroup;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentDropoutAndReasonTest extends TestCase
{
    use RefreshDatabase;

    public function test_input_data_form_and_student_profile_support_dropout_and_status_reason(): void
    {
        $admin = Admin::create([
            'username' => 'admin_dropout_crud',
            'password' => Hash::make('Password1!'),
        ]);
        $this->actingAs($admin);

        $batch = Batch::create(['batch_name' => 'PPTI20']);
        $classGroup = ClassGroup::create(['batch_id' => $batch->id, 'class_code' => 'PPTI 20A']);

        $this->post(route('input-data.store', 'student'), [
            'nim' => '2910000001',
            'name' => 'Student Dropout',
            'class_id' => $classGroup->id,
            'status' => 'Dropout',
            'status_reason' => 'Transferred to another university',
        ])->assertRedirect();

        $student = Student::where('nim', '2910000001')->firstOrFail();
        $this->assertSame('Dropout', $student->status);
        $this->assertSame('Transferred to another university', $student->status_reason);

        $listResponse = $this->get(route('students.index', ['status' => ['Dropout']]));
        $listResponse->assertOk();
        $listResponse->assertSee('Student Dropout');
        $listResponse->assertSee('Dropout');

        $profileResponse = $this->get(route('students.profile', $student->id));
        $profileResponse->assertOk();
        $profileResponse->assertSee('Dropout');
        $profileResponse->assertSee('Transferred to another university');

        // Update to On Leave with a new reason
        $this->put(route('students.update', $student->id), [
            'nim' => '2910000001',
            'name' => 'Student Dropout',
            'class_id' => $classGroup->id,
            'status' => 'On Leave',
            'status_reason' => 'Medical leave for one semester',
        ])->assertRedirect();

        $student->refresh();
        $this->assertSame('On Leave', $student->status);
        $this->assertSame('Medical leave for one semester', $student->status_reason);

        $exportList = $this->get(route('students.export'))->streamedContent();
        $this->assertStringContainsString('Reason', $exportList);
        $this->assertStringContainsString('Medical leave for one semester', $exportList);

        $exportProfile = $this->get(route('students.profile.export', $student->id))->streamedContent();
        $this->assertStringContainsString('Reason', $exportProfile);
        $this->assertStringContainsString('Medical leave for one semester', $exportProfile);

        // Switching back to Active clears status_reason
        $this->put(route('students.update', $student->id), [
            'nim' => '2910000001',
            'name' => 'Student Dropout',
            'class_id' => $classGroup->id,
            'status' => 'Active',
            'status_reason' => 'Should be cleared',
        ])->assertRedirect();

        $student->refresh();
        $this->assertSame('Active', $student->status);
        $this->assertNull($student->status_reason);
    }

    public function test_student_csv_import_supports_dropout_and_status_reason(): void
    {
        $admin = Admin::create([
            'username' => 'admin_dropout_csv',
            'password' => Hash::make('Password1!'),
        ]);
        $this->actingAs($admin);

        $csv = implode("\n", [
            'nim,name,class_code,batch,status,status_reason',
            '2920000001,CSV Dropout,PPTI 20A,PPTI20,Dropout,Personal reasons',
            '2920000002,CSV Leave,PPTI 20A,PPTI20,On Leave,Family emergency',
        ]);

        $file = UploadedFile::fake()->createWithContent('students_status.csv', $csv);
        $preview = $this->postJson(route('input-data.import.preview', 'student'), ['file' => $file]);
        $preview->assertOk()->assertJsonPath('valid', 2)->assertJsonPath('invalid', 0);

        $this->postJson(route('input-data.import.commit', 'student'), ['token' => $preview->json('token')])
            ->assertOk()
            ->assertJsonPath('imported', 2);

        $dropout = Student::where('nim', '2920000001')->firstOrFail();
        $leave = Student::where('nim', '2920000002')->firstOrFail();
        $this->assertSame('Dropout', $dropout->status);
        $this->assertSame('Personal reasons', $dropout->status_reason);
        $this->assertSame('On Leave', $leave->status);
        $this->assertSame('Family emergency', $leave->status_reason);

        // Re-import: blank reason retains existing reason for Dropout; switching to Active clears reason
        $reimportCsv = implode("\n", [
            'nim,name,class_code,batch,status,status_reason',
            '2920000001,CSV Dropout,PPTI 20A,PPTI20,Dropout,',
            '2920000002,CSV Leave,PPTI 20A,PPTI20,Active,',
        ]);

        $reimportFile = UploadedFile::fake()->createWithContent('students_status_reimport.csv', $reimportCsv);
        $reimportPreview = $this->postJson(route('input-data.import.preview', 'student'), ['file' => $reimportFile]);
        $reimportPreview->assertOk()->assertJsonPath('valid', 2)->assertJsonPath('invalid', 0);

        $this->postJson(route('input-data.import.commit', 'student'), ['token' => $reimportPreview->json('token')])
            ->assertOk()
            ->assertJsonPath('imported', 2);

        $dropout->refresh();
        $leave->refresh();
        $this->assertSame('Personal reasons', $dropout->status_reason);
        $this->assertSame('Active', $leave->status);
        $this->assertNull($leave->status_reason);
    }
}
