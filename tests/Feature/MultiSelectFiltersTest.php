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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MultiSelectFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_list_supports_multi_select_status_and_class_filters(): void
    {
        $admin = Admin::create([
            'username' => 'admin_students_ms',
            'password' => Hash::make('Password1!'),
        ]);
        $this->actingAs($admin);

        $batch = Batch::create(['batch_name' => 'PPTI20']);
        $classA = ClassGroup::create(['batch_id' => $batch->id, 'class_code' => 'PPTI 18A']);
        $classB = ClassGroup::create(['batch_id' => $batch->id, 'class_code' => 'PPTI 19A']);
        $classC = ClassGroup::create(['batch_id' => $batch->id, 'class_code' => 'PPTI 20A']);

        Student::create([
            'nim' => '2900000001',
            'name' => 'Student Active A',
            'class_id' => $classA->id,
            'status' => 'Active',
            'cumulative_gpa' => 3.50,
        ]);
        Student::create([
            'nim' => '2900000002',
            'name' => 'Student Graduated B',
            'class_id' => $classB->id,
            'status' => 'Graduated',
            'cumulative_gpa' => 3.80,
        ]);
        Student::create([
            'nim' => '2900000003',
            'name' => 'Student Leave C',
            'class_id' => $classC->id,
            'status' => 'On Leave',
            'cumulative_gpa' => 3.10,
        ]);
        Student::create([
            'nim' => '2900000004',
            'name' => 'Student Active C',
            'class_id' => $classC->id,
            'status' => 'Active',
            'cumulative_gpa' => 3.65,
        ]);

        $statusResponse = $this->get(route('students.index', ['status' => ['Active', 'Graduated']]));
        $statusResponse->assertOk();
        $statusResponse->assertSee('Student Active A');
        $statusResponse->assertSee('Student Graduated B');
        $statusResponse->assertSee('Student Active C');
        $statusResponse->assertDontSee('Student Leave C');
        $statusResponse->assertSee('aria-expanded="true"', false);
        $statusResponse->assertSee('name="status[]"', false);
        $statusResponse->assertSee('name="class[]"', false);

        $classResponse = $this->get(route('students.index', ['class' => ['PPTI 18A', 'PPTI 20A']]));
        $classResponse->assertOk();
        $classResponse->assertSee('Student Active A');
        $classResponse->assertSee('Student Leave C');
        $classResponse->assertSee('Student Active C');
        $classResponse->assertDontSee('Student Graduated B');

        $combinedResponse = $this->get(route('students.index', [
            'status' => ['Active', 'Graduated'],
            'class' => ['PPTI 18A', 'PPTI 20A'],
        ]));
        $combinedResponse->assertOk();
        $combinedResponse->assertSee('Student Active A');
        $combinedResponse->assertSee('Student Active C');
        $combinedResponse->assertDontSee('Student Graduated B');
        $combinedResponse->assertDontSee('Student Leave C');
    }

    public function test_lecturer_list_supports_multi_select_status_jja_and_education_filters(): void
    {
        $admin = Admin::create([
            'username' => 'admin_lecturers_ms',
            'password' => Hash::make('Password1!'),
        ]);
        $this->actingAs($admin);

        Lecturer::create([
            'lecturer_code' => 'D8001',
            'name' => 'Dr. One',
            'status' => 'Active',
            'jja' => 'Asisten Ahli',
            'latest_education' => 'Master',
        ]);
        Lecturer::create([
            'lecturer_code' => 'D8002',
            'name' => 'Dr. Two',
            'status' => 'Inactive',
            'jja' => 'Lektor',
            'latest_education' => 'Doctoral',
        ]);
        Lecturer::create([
            'lecturer_code' => 'D8003',
            'name' => 'Dr. Three',
            'status' => 'Active',
            'jja' => 'Guru Besar',
            'latest_education' => 'Bachelor',
        ]);

        $statusResponse = $this->get(route('lecturers.index', ['status' => ['Active', 'Inactive']]));
        $statusResponse->assertOk();
        $statusResponse->assertSee('Dr. One');
        $statusResponse->assertSee('Dr. Two');
        $statusResponse->assertSee('Dr. Three');
        $statusResponse->assertSee('aria-expanded="true"', false);
        $statusResponse->assertSee('name="status[]"', false);
        $statusResponse->assertSee('name="jja[]"', false);
        $statusResponse->assertSee('name="latest_education[]"', false);

        $jjaResponse = $this->get(route('lecturers.index', ['jja' => ['Asisten Ahli', 'Guru Besar']]));
        $jjaResponse->assertOk();
        $jjaResponse->assertSee('Dr. One');
        $jjaResponse->assertSee('Dr. Three');
        $jjaResponse->assertDontSee('Dr. Two');
        $jjaResponse->assertSee('aria-expanded="true"', false);

        $eduResponse = $this->get(route('lecturers.index', ['latest_education' => ['Master', 'Doctoral']]));
        $eduResponse->assertOk();
        $eduResponse->assertSee('Dr. One');
        $eduResponse->assertSee('Dr. Two');
        $eduResponse->assertDontSee('Dr. Three');
        $eduResponse->assertSee('aria-expanded="true"', false);
    }

    public function test_course_records_list_supports_multi_select_and_scalar_filters(): void
    {
        $admin = Admin::create([
            'username' => 'admin_cr_ms',
            'password' => Hash::make('Password1!'),
        ]);
        $this->actingAs($admin);

        $batch = Batch::create(['batch_name' => 'PPTI20']);
        $class1 = ClassGroup::create(['batch_id' => $batch->id, 'class_code' => 'LA01']);
        $class2 = ClassGroup::create(['batch_id' => $batch->id, 'class_code' => 'LB01']);
        $class3 = ClassGroup::create(['batch_id' => $batch->id, 'class_code' => 'LC01']);

        $period1 = Period::create(['period_name' => '2025 - Odd', 'start_date' => '2025-09-01', 'end_date' => '2026-01-31']);
        $period2 = Period::create(['period_name' => '2025 - Even', 'start_date' => '2026-02-01', 'end_date' => '2026-06-30']);
        $period3 = Period::create(['period_name' => '2026 - Odd', 'start_date' => '2026-09-01', 'end_date' => '2027-01-31']);

        $course1 = Course::create(['course_code' => 'COMP7001', 'course_name' => 'Course One', 'sks' => '4']);
        $course2 = Course::create(['course_code' => 'COMP7002', 'course_name' => 'Course Two', 'sks' => '4']);
        $course3 = Course::create(['course_code' => 'COMP7003', 'course_name' => 'Course Three', 'sks' => '4']);

        $lecturer1 = Lecturer::create(['lecturer_code' => 'D9101', 'name' => 'Lec One', 'status' => 'Active']);
        $lecturer2 = Lecturer::create(['lecturer_code' => 'D9102', 'name' => 'Lec Two', 'status' => 'Active']);
        $lecturer3 = Lecturer::create(['lecturer_code' => 'D9103', 'name' => 'Lec Three', 'status' => 'Active']);

        CourseRecord::create([
            'record_code' => 'CR-101',
            'course_id' => $course1->id,
            'period_id' => $period1->id,
            'class_id' => $class1->id,
            'lecturer_id' => $lecturer1->id,
        ]);
        CourseRecord::create([
            'record_code' => 'CR-102',
            'course_id' => $course2->id,
            'period_id' => $period2->id,
            'class_id' => $class2->id,
            'lecturer_id' => $lecturer2->id,
        ]);
        CourseRecord::create([
            'record_code' => 'CR-103',
            'course_id' => $course3->id,
            'period_id' => $period3->id,
            'class_id' => $class3->id,
            'lecturer_id' => $lecturer3->id,
        ]);

        $periodResponse = $this->get(route('course-records.index', ['period' => ['2025 - Odd', '2026 - Odd']]));
        $periodResponse->assertOk();
        $periodResponse->assertSee('CR-101');
        $periodResponse->assertSee('CR-103');
        $periodResponse->assertDontSee('CR-102');
        $periodResponse->assertSee('aria-expanded="true"', false);
        $periodResponse->assertSee('name="period[]"', false);
        $periodResponse->assertSee('name="class[]"', false);
        $periodResponse->assertSee('name="lecturer_code[]"', false);
        $periodResponse->assertSee('name="course[]"', false);

        $classResponse = $this->get(route('course-records.index', ['class' => ['LA01', 'LB01']]));
        $classResponse->assertOk();
        $classResponse->assertSee('CR-101');
        $classResponse->assertSee('CR-102');
        $classResponse->assertDontSee('CR-103');

        $lecturerResponse = $this->get(route('course-records.index', ['lecturer_code' => ['D9102', 'D9103']]));
        $lecturerResponse->assertOk();
        $lecturerResponse->assertSee('CR-102');
        $lecturerResponse->assertSee('CR-103');
        $lecturerResponse->assertDontSee('CR-101');

        $courseResponse = $this->get(route('course-records.index', ['course' => ['COMP7001', 'COMP7003']]));
        $courseResponse->assertOk();
        $courseResponse->assertSee('CR-101');
        $courseResponse->assertSee('CR-103');
        $courseResponse->assertDontSee('CR-102');

        $scalarResponse = $this->get(route('course-records.index', ['period' => '2025 - Even']));
        $scalarResponse->assertOk();
        $scalarResponse->assertSee('CR-102');
        $scalarResponse->assertDontSee('CR-101');
        $scalarResponse->assertDontSee('CR-103');

        $exportCsv = $this->get(route('course-records.export', ['course' => ['COMP7001', 'COMP7003']]))->streamedContent();
        $this->assertStringContainsString('CR-101', $exportCsv);
        $this->assertStringContainsString('CR-103', $exportCsv);
        $this->assertStringNotContainsString('CR-102', $exportCsv);
    }

    public function test_audit_logs_support_multi_select_admin_and_action_filters(): void
    {
        $admin1 = Admin::create(['username' => 'audit_admin_one', 'password' => Hash::make('Password1!')]);
        $admin2 = Admin::create(['username' => 'audit_admin_two', 'password' => Hash::make('Password1!')]);
        $admin3 = Admin::create(['username' => 'audit_admin_three', 'password' => Hash::make('Password1!')]);
        $this->actingAs($admin1);

        AuditLog::create([
            'admin_id' => $admin1->id,
            'action_type' => 'CREATE',
            'target_entity' => 'Student',
            'target_id' => 9001,
            'new_value' => json_encode(['marker' => 'marker_audit_1']),
        ]);
        AuditLog::create([
            'admin_id' => $admin2->id,
            'action_type' => 'UPDATE',
            'target_entity' => 'Lecturer',
            'target_id' => 9002,
            'old_value' => json_encode(['marker' => 'old_2']),
            'new_value' => json_encode(['marker' => 'marker_audit_2']),
        ]);
        AuditLog::create([
            'admin_id' => $admin3->id,
            'action_type' => 'DELETE',
            'target_entity' => 'CourseRecord',
            'target_id' => 9003,
            'old_value' => json_encode(['marker' => 'marker_audit_3']),
        ]);

        $adminResponse = $this->get(route('audit-logs.index', ['admin_id' => [$admin1->id, $admin3->id]]));
        $adminResponse->assertOk();
        $adminResponse->assertSee('marker_audit_1');
        $adminResponse->assertSee('marker_audit_3');
        $adminResponse->assertDontSee('marker_audit_2');
        $adminResponse->assertSee('name="admin_id[]"', false);
        $adminResponse->assertSee('name="action_type[]"', false);

        $actionResponse = $this->get(route('audit-logs.index', ['action_type' => ['CREATE', 'UPDATE']]));
        $actionResponse->assertOk();
        $actionResponse->assertSee('marker_audit_1');
        $actionResponse->assertSee('marker_audit_2');
        $actionResponse->assertDontSee('marker_audit_3');

        $exportCsv = $this->get(route('audit-logs.export', [
            'admin_id' => [$admin1->id, $admin2->id],
            'action_type' => ['CREATE', 'UPDATE'],
        ]))->streamedContent();
        $this->assertStringContainsString('marker_audit_1', $exportCsv);
        $this->assertStringContainsString('marker_audit_2', $exportCsv);
        $this->assertStringNotContainsString('marker_audit_3', $exportCsv);
    }
}
