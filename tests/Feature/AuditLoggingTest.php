<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\ClassGroup;
use App\Models\Course;
use App\Models\CourseRecord;
use App\Models\Lecturer;
use App\Models\Period;
use App\Models\Student;
use App\Services\InputDataImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuditLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_input_data_forms_record_audit_logs_for_all_entities(): void
    {
        $admin = Admin::create([
            'username' => 'admin_audit',
            'password' => Hash::make('Password1!'),
        ]);
        $this->actingAs($admin);

        $this->post(route('input-data.store', 'batch'), ['batch_name' => 'PPTI 21'])->assertRedirect();
        $batch = Batch::where('batch_name', 'PPTI 21')->firstOrFail();
        $this->assertDatabaseHas('audit_logs', [
            'admin_id' => $admin->id,
            'action_type' => 'CREATE',
            'target_entity' => 'Batch',
            'target_id' => $batch->id,
        ]);

        $this->post(route('input-data.store', 'period'), [
            'period_name' => '2026 - Odd',
            'start_date' => '2026-09-01',
            'end_date' => '2027-01-31',
        ])->assertRedirect();
        $period = Period::where('period_name', '2026 - Odd')->firstOrFail();
        $this->assertDatabaseHas('audit_logs', [
            'action_type' => 'CREATE',
            'target_entity' => 'Period',
            'target_id' => $period->id,
        ]);

        $this->post(route('input-data.store', 'class'), [
            'batch_id' => $batch->id,
            'class_code' => 'PPTI 21A',
        ])->assertRedirect();
        $classGroup = ClassGroup::where('class_code', 'PPTI 21A')->firstOrFail();
        $this->assertDatabaseHas('audit_logs', [
            'action_type' => 'CREATE',
            'target_entity' => 'ClassGroup',
            'target_id' => $classGroup->id,
        ]);

        $this->post(route('input-data.store', 'course'), [
            'course_code' => 'COMP6100',
            'course_name' => 'Software Engineering',
            'sks' => '4',
        ])->assertRedirect();
        $course = Course::where('course_code', 'COMP6100')->firstOrFail();
        $this->assertDatabaseHas('audit_logs', [
            'action_type' => 'CREATE',
            'target_entity' => 'Course',
            'target_id' => $course->id,
        ]);

        $this->post(route('input-data.store', 'lecturer'), [
            'lecturer_code' => 'D9001',
            'name' => 'Dr. Lecturer',
            'status' => 'Active',
        ])->assertRedirect();
        $lecturer = Lecturer::where('lecturer_code', 'D9001')->firstOrFail();
        $this->assertDatabaseHas('audit_logs', [
            'action_type' => 'CREATE',
            'target_entity' => 'Lecturer',
            'target_id' => $lecturer->id,
        ]);

        $this->post(route('input-data.store', 'student'), [
            'nim' => '2700000101',
            'name' => 'Student One',
            'class_id' => $classGroup->id,
            'status' => 'Active',
        ])->assertRedirect();
        $student = Student::where('nim', '2700000101')->firstOrFail();
        $this->assertDatabaseHas('audit_logs', [
            'action_type' => 'CREATE',
            'target_entity' => 'Student',
            'target_id' => $student->id,
        ]);

        $this->post(route('input-data.store', 'course-record'), [
            'course_id' => $course->id,
            'period_id' => $period->id,
            'class_id' => $classGroup->id,
            'lecturer_id' => $lecturer->id,
            'ikadq' => '3.75',
            'students' => [$student->id],
        ])->assertRedirect();
        $record = CourseRecord::firstOrFail();
        $this->assertDatabaseHas('audit_logs', [
            'action_type' => 'CREATE',
            'target_entity' => 'CourseRecord',
            'target_id' => $record->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action_type' => 'CREATE',
            'target_entity' => 'StudentGrade',
        ]);

        $this->post(route('input-data.store', 'grades'), [
            'course_record_id' => $record->id,
            'grades' => [$student->id => 'A'],
        ])->assertRedirect();
        $this->assertDatabaseHas('audit_logs', [
            'action_type' => 'UPDATE',
            'target_entity' => 'StudentGrade',
        ]);
    }

    public function test_course_record_updates_and_enrollments_are_logged(): void
    {
        $admin = Admin::create([
            'username' => 'admin_cr',
            'password' => Hash::make('Password1!'),
        ]);
        $this->actingAs($admin);

        $batch = Batch::create(['batch_name' => 'PPTI 22']);
        $classGroup = ClassGroup::create(['batch_id' => $batch->id, 'class_code' => 'PPTI 22A']);
        $period = Period::create(['period_name' => '2026 - Even', 'start_date' => '2026-02-01']);
        $course = Course::create(['course_code' => 'COMP6200', 'course_name' => 'Algorithms', 'sks' => '4']);
        $lecturer1 = Lecturer::create(['lecturer_code' => 'D1001', 'name' => 'Dr. One', 'status' => 'Active']);
        $lecturer2 = Lecturer::create(['lecturer_code' => 'D1002', 'name' => 'Dr. Two', 'status' => 'Active']);
        $student1 = Student::create(['nim' => '2700000201', 'name' => 'S1', 'class_id' => $classGroup->id, 'status' => 'Active', 'cumulative_gpa' => 0]);
        $student2 = Student::create(['nim' => '2700000202', 'name' => 'S2', 'class_id' => $classGroup->id, 'status' => 'Active', 'cumulative_gpa' => 0]);
        $record = CourseRecord::create([
            'record_code' => 'CR-2026-1',
            'course_id' => $course->id,
            'period_id' => $period->id,
            'class_id' => $classGroup->id,
            'lecturer_id' => $lecturer1->id,
        ]);

        $this->put(route('course-records.update', $record->id), [
            'course_id' => $course->id,
            'period_id' => $period->id,
            'class_id' => $classGroup->id,
            'lecturer_id' => $lecturer2->id,
            'ikadq' => '3.50',
        ])->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'action_type' => 'UPDATE',
            'target_entity' => 'CourseRecord',
            'target_id' => $record->id,
        ]);

        $this->post(route('course-records.students.store', $record->id), [
            'students' => [$student1->id, $student2->id],
        ])->assertRedirect();

        $this->assertSame(2, AuditLog::where('action_type', 'CREATE')->where('target_entity', 'StudentGrade')->count());

        $this->delete(route('course-records.students.destroy', [$record->id, $student1->id]))->assertRedirect();
        $this->assertSame(1, AuditLog::where('action_type', 'DELETE')->where('target_entity', 'StudentGrade')->count());

        $this->delete(route('course-records.students.bulk-destroy', $record->id), [
            'students' => [$student2->id],
        ])->assertRedirect();
        $this->assertSame(2, AuditLog::where('action_type', 'DELETE')->where('target_entity', 'StudentGrade')->count());
    }

    public function test_csv_importer_records_audit_logs_for_all_import_tabs(): void
    {
        $admin = Admin::create([
            'username' => 'admin_import',
            'password' => Hash::make('Password1!'),
        ]);
        $this->actingAs($admin);

        $importer = app(InputDataImporter::class);

        $importer->create('lecturer', [
            'lecturer_code' => 'D3001',
            'name' => 'Imported Lecturer',
            'status' => 'Active',
        ]);
        $importer->create('course', [
            'course_code' => 'COMP6300',
            'course_name' => 'Imported Course',
            'sks' => '2',
        ]);
        $importer->create('class', [
            '_batch_name' => 'PPTI 23',
            'class_code' => 'PPTI 23A',
        ]);

        $batch = Batch::where('batch_name', 'PPTI 23')->firstOrFail();
        $classGroup = ClassGroup::where('class_code', 'PPTI 23A')->firstOrFail();
        $lecturer = Lecturer::where('lecturer_code', 'D3001')->firstOrFail();
        $course = Course::where('course_code', 'COMP6300')->firstOrFail();
        $period = Period::create(['period_name' => '2027 - Odd', 'start_date' => '2027-09-01']);
        $student = Student::create(['nim' => '2700000301', 'name' => 'Imported Student', 'class_id' => $classGroup->id, 'status' => 'Active', 'cumulative_gpa' => 0]);

        $importer->create('course-record', [
            'course_id' => $course->id,
            'period_id' => $period->id,
            'class_id' => $classGroup->id,
            'lecturer_id' => $lecturer->id,
            'ikadq' => '3.90',
            'students' => [$student->id],
        ]);

        $this->assertDatabaseHas('audit_logs', ['action_type' => 'CREATE', 'target_entity' => 'Lecturer', 'target_id' => $lecturer->id]);
        $this->assertDatabaseHas('audit_logs', ['action_type' => 'CREATE', 'target_entity' => 'Course', 'target_id' => $course->id]);
        $this->assertDatabaseHas('audit_logs', ['action_type' => 'CREATE', 'target_entity' => 'Batch', 'target_id' => $batch->id]);
        $this->assertDatabaseHas('audit_logs', ['action_type' => 'CREATE', 'target_entity' => 'ClassGroup', 'target_id' => $classGroup->id]);
        $this->assertDatabaseHas('audit_logs', ['action_type' => 'CREATE', 'target_entity' => 'CourseRecord']);
        $this->assertDatabaseHas('audit_logs', ['action_type' => 'CREATE', 'target_entity' => 'StudentGrade']);
    }

    public function test_auth_events_are_logged_without_exposing_passwords(): void
    {
        $admin = Admin::create([
            'username' => 'admin_auth',
            'password' => Hash::make('OldPass1!'),
        ]);

        $this->post(route('login.submit'), [
            'username' => 'admin_auth',
            'password' => 'OldPass1!',
        ])->assertRedirect(route('students.index'));

        $this->assertDatabaseHas('audit_logs', [
            'admin_id' => $admin->id,
            'action_type' => 'LOGIN',
            'target_entity' => 'Admin',
            'target_id' => $admin->id,
        ]);

        $this->put(route('password.update'), [
            'current_password' => 'OldPass1!',
            'password' => 'NewPass1!',
            'password_confirmation' => 'NewPass1!',
        ])->assertRedirect(route('students.index'));

        $this->assertDatabaseHas('audit_logs', [
            'admin_id' => $admin->id,
            'action_type' => 'UPDATE_PASSWORD',
            'target_entity' => 'Admin',
            'target_id' => $admin->id,
        ]);

        $this->post(route('logout'))->assertRedirect('/login');

        $this->assertDatabaseHas('audit_logs', [
            'admin_id' => $admin->id,
            'action_type' => 'LOGOUT',
            'target_entity' => 'Admin',
            'target_id' => $admin->id,
        ]);

        $allLogValues = AuditLog::all()->pluck('new_value')->merge(AuditLog::all()->pluck('old_value'))->implode(' ');
        $this->assertStringNotContainsString('OldPass1!', $allLogValues);
        $this->assertStringNotContainsString('NewPass1!', $allLogValues);
    }
}
