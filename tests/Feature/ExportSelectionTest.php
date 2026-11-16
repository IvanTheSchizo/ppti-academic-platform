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
use App\Models\StudentGrade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportSelectionTest extends TestCase
{
    use RefreshDatabase;

    private function createFixtures(): array
    {
        $admin = Admin::create([
            'username' => 'admin_test',
            'password' => bcrypt('password'),
        ]);

        $batch = Batch::create(['batch_name' => 'PPTI 20']);
        $classGroup = ClassGroup::create(['batch_id' => $batch->id, 'class_code' => 'PPTI 20A']);
        $period = Period::create([
            'period_name' => '2026 - Odd',
            'start_date' => '2026-09-01',
            'end_date' => '2027-01-31',
        ]);
        $course1 = Course::create(['course_code' => 'COMP6001', 'course_name' => 'Algorithm Design', 'sks' => '4']);
        $course2 = Course::create(['course_code' => 'COMP6002', 'course_name' => 'Database Systems', 'sks' => '4']);

        $lecturer1 = Lecturer::create([
            'nip' => '1001',
            'lecturer_code' => 'D0001',
            'name' => 'Dr. Alpha',
            'email_binus_edu' => 'alpha@binus.edu',
            'status' => 'Active',
        ]);
        $lecturer2 = Lecturer::create([
            'nip' => '1002',
            'lecturer_code' => 'D0002',
            'name' => 'Dr. Beta',
            'email_binus_edu' => 'beta@binus.edu',
            'status' => 'Active',
        ]);

        $student1 = Student::create([
            'nim' => '2700000001',
            'name' => 'Alice Student',
            'class_id' => $classGroup->id,
            'status' => 'Active',
            'cumulative_gpa' => 3.85,
        ]);
        $student2 = Student::create([
            'nim' => '2700000002',
            'name' => 'Bob Student',
            'class_id' => $classGroup->id,
            'status' => 'Active',
            'cumulative_gpa' => 3.40,
        ]);

        $record1 = CourseRecord::create([
            'record_code' => 'CR-001',
            'course_id' => $course1->id,
            'period_id' => $period->id,
            'class_id' => $classGroup->id,
            'lecturer_id' => $lecturer1->id,
        ]);
        $record2 = CourseRecord::create([
            'record_code' => 'CR-002',
            'course_id' => $course2->id,
            'period_id' => $period->id,
            'class_id' => $classGroup->id,
            'lecturer_id' => $lecturer1->id,
        ]);

        $grade1 = StudentGrade::create([
            'student_id' => $student1->id,
            'course_record_id' => $record1->id,
            'letter_grade' => 'A',
        ]);
        $grade2 = StudentGrade::create([
            'student_id' => $student2->id,
            'course_record_id' => $record1->id,
            'letter_grade' => 'B',
        ]);
        $grade3 = StudentGrade::create([
            'student_id' => $student1->id,
            'course_record_id' => $record2->id,
            'letter_grade' => 'A-',
        ]);

        $log1 = AuditLog::create([
            'admin_id' => $admin->id,
            'action_type' => 'CREATE',
            'target_entity' => 'Student',
            'target_id' => $student1->id,
            'old_value' => null,
            'new_value' => '{"name":"Alice Student"}',
        ]);
        $log2 = AuditLog::create([
            'admin_id' => $admin->id,
            'action_type' => 'CREATE',
            'target_entity' => 'Student',
            'target_id' => $student2->id,
            'old_value' => null,
            'new_value' => '{"name":"Bob Student"}',
        ]);

        return compact(
            'admin',
            'student1',
            'student2',
            'lecturer1',
            'lecturer2',
            'record1',
            'record2',
            'grade1',
            'grade2',
            'grade3',
            'log1',
            'log2'
        );
    }

    public function test_download_pages_render_download_popup_modal_with_checkboxes(): void
    {
        $data = $this->createFixtures();
        $this->actingAs($data['admin']);

        $pages = [
            route('students.index'),
            route('students.profile', [$data['student1']->id, 'tab' => 'grades']),
            route('lecturers.index'),
            route('lecturers.profile', [$data['lecturer1']->id, 'tab' => 'courses']),
            route('course-records.index'),
            route('course-records.show', $data['record1']->id),
            route('audit-logs.index'),
        ];

        foreach ($pages as $url) {
            $response = $this->get($url);
            $response->assertOk();
            $response->assertSee('data-download-modal', false);
            $response->assertSee('data-download-select-all', false);
            $response->assertSee('data-download-select-all-btn', false);
            $response->assertSee('data-download-deselect-all-btn', false);
            $response->assertSee('data-download-cb', false);
            $response->assertSee('data-download-submit', false);
        }
    }

    public function test_students_export_filters_by_selected_ids_or_downloads_all_when_none_selected(): void
    {
        $data = $this->createFixtures();
        $this->actingAs($data['admin']);

        $allCsv = $this->get(route('students.export'))->streamedContent();
        $this->assertStringContainsString('Alice Student', $allCsv);
        $this->assertStringContainsString('Bob Student', $allCsv);

        $selectedCsv = $this->get(route('students.export', ['ids' => [$data['student1']->id]]))->streamedContent();
        $this->assertStringContainsString('Alice Student', $selectedCsv);
        $this->assertStringNotContainsString('Bob Student', $selectedCsv);
    }

    public function test_lecturers_export_filters_by_selected_ids_and_profile_export_works(): void
    {
        $data = $this->createFixtures();
        $this->actingAs($data['admin']);

        $selectedCsv = $this->get(route('lecturers.export', ['ids' => [$data['lecturer1']->id]]))->streamedContent();
        $this->assertStringContainsString('Dr. Alpha', $selectedCsv);
        $this->assertStringNotContainsString('Dr. Beta', $selectedCsv);

        $coursesCsv = $this->get(route('lecturers.profile.export', [
            $data['lecturer1']->id,
            'tab' => 'courses',
            'ids' => [$data['record1']->id],
        ]))->streamedContent();
        $this->assertStringContainsString('Algorithm Design', $coursesCsv);
        $this->assertStringNotContainsString('Database Systems', $coursesCsv);
    }

    public function test_course_records_and_detail_export_filter_by_selected_ids(): void
    {
        $data = $this->createFixtures();
        $this->actingAs($data['admin']);

        $recordsCsv = $this->get(route('course-records.export', ['ids' => [$data['record2']->id]]))->streamedContent();
        $this->assertStringContainsString('CR-002', $recordsCsv);
        $this->assertStringNotContainsString('CR-001', $recordsCsv);

        $detailCsv = $this->get(route('course-records.show.export', [
            $data['record1']->id,
            'ids' => [$data['student2']->id],
        ]))->streamedContent();
        $this->assertStringContainsString('Bob Student', $detailCsv);
        $this->assertStringNotContainsString('Alice Student', $detailCsv);
    }

    public function test_student_grades_and_audit_logs_export_filter_by_selected_ids(): void
    {
        $data = $this->createFixtures();
        $this->actingAs($data['admin']);

        $gradesCsv = $this->get(route('students.profile.export', [
            $data['student1']->id,
            'tab' => 'grades',
            'ids' => [$data['grade1']->id],
        ]))->streamedContent();
        $this->assertStringContainsString('Algorithm Design', $gradesCsv);
        $this->assertStringNotContainsString('Database Systems', $gradesCsv);

        $logsCsv = $this->get(route('audit-logs.export', ['ids' => [$data['log2']->id]]))->streamedContent();
        $this->assertStringContainsString('Bob Student', $logsCsv);
        $this->assertStringNotContainsString('Alice Student', $logsCsv);
    }
}
