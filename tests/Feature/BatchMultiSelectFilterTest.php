<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Batch;
use App\Models\ClassGroup;
use App\Models\Lecturer;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BatchMultiSelectFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_list_supports_multi_batch_and_scalar_batch_filters(): void
    {
        $admin = Admin::create([
            'username' => 'admin_student_multibatch',
            'password' => Hash::make('Password1!'),
        ]);
        $this->actingAs($admin);

        $batch18 = Batch::create(['batch_name' => 'PPTI18']);
        $batch19 = Batch::create(['batch_name' => 'PPTI19']);
        $batch20 = Batch::create(['batch_name' => 'PPTI20']);

        $class18 = ClassGroup::create(['batch_id' => $batch18->id, 'class_code' => 'PPTI 18A']);
        $class19 = ClassGroup::create(['batch_id' => $batch19->id, 'class_code' => 'PPTI 19A']);
        $class20 = ClassGroup::create(['batch_id' => $batch20->id, 'class_code' => 'PPTI 20A']);

        Student::create([
            'nim' => '2800000001',
            'name' => 'Student Eighteen',
            'class_id' => $class18->id,
            'status' => 'Active',
            'cumulative_gpa' => 3.50,
        ]);
        Student::create([
            'nim' => '2800000002',
            'name' => 'Student Nineteen',
            'class_id' => $class19->id,
            'status' => 'Active',
            'cumulative_gpa' => 3.60,
        ]);
        Student::create([
            'nim' => '2800000003',
            'name' => 'Student Twenty',
            'class_id' => $class20->id,
            'status' => 'Active',
            'cumulative_gpa' => 3.70,
        ]);

        $multiResponse = $this->get(route('students.index', ['batch' => ['PPTI18', 'PPTI20']]));
        $multiResponse->assertOk();
        $multiResponse->assertSee('Student Eighteen');
        $multiResponse->assertSee('Student Twenty');
        $multiResponse->assertDontSee('Student Nineteen');
        $multiResponse->assertSee('aria-expanded="true"', false);
        $multiResponse->assertSee('name="batch[]"', false);

        $scalarResponse = $this->get(route('students.index', ['batch' => 'PPTI19']));
        $scalarResponse->assertOk();
        $scalarResponse->assertSee('Student Nineteen');
        $scalarResponse->assertDontSee('Student Eighteen');
        $scalarResponse->assertDontSee('Student Twenty');
        $scalarResponse->assertSee('aria-expanded="true"', false);

        $exportCsv = $this->get(route('students.export', ['batch' => ['PPTI18', 'PPTI20']]))->streamedContent();
        $this->assertStringContainsString('Student Eighteen', $exportCsv);
        $this->assertStringContainsString('Student Twenty', $exportCsv);
        $this->assertStringNotContainsString('Student Nineteen', $exportCsv);
    }

    public function test_lecturer_list_supports_multi_batch_filter_without_duplicate_rows(): void
    {
        $admin = Admin::create([
            'username' => 'admin_lecturer_multibatch',
            'password' => Hash::make('Password1!'),
        ]);
        $this->actingAs($admin);

        $batch18 = Batch::create(['batch_name' => 'PPTI18']);
        $batch19 = Batch::create(['batch_name' => 'PPTI19']);
        $batch20 = Batch::create(['batch_name' => 'PPTI20']);

        $lecturerOverlap = Lecturer::create([
            'lecturer_code' => 'D7001',
            'name' => 'Dr. Overlap',
            'status' => 'Active',
        ]);
        $lecturerOverlap->batches()->sync([$batch18->id, $batch20->id]);

        $lecturerNineteen = Lecturer::create([
            'lecturer_code' => 'D7002',
            'name' => 'Dr. Nineteen',
            'status' => 'Active',
        ]);
        $lecturerNineteen->batches()->sync([$batch19->id]);

        $lecturerTwenty = Lecturer::create([
            'lecturer_code' => 'D7003',
            'name' => 'Dr. Twenty',
            'status' => 'Active',
        ]);
        $lecturerTwenty->batches()->sync([$batch20->id]);

        $multiResponse = $this->get(route('lecturers.index', ['batch' => ['PPTI18', 'PPTI20']]));
        $multiResponse->assertOk();
        $multiResponse->assertSee('Dr. Overlap');
        $multiResponse->assertSee('Dr. Twenty');
        $multiResponse->assertDontSee('Dr. Nineteen');
        $this->assertSame(1, substr_count($multiResponse->getContent(), route('lecturers.profile', $lecturerOverlap->id)));
        $multiResponse->assertSee('aria-expanded="true"', false);
        $multiResponse->assertSee('name="batch[]"', false);

        $scalarResponse = $this->get(route('lecturers.index', ['batch' => 'PPTI19']));
        $scalarResponse->assertOk();
        $scalarResponse->assertSee('Dr. Nineteen');
        $scalarResponse->assertDontSee('Dr. Overlap');
        $scalarResponse->assertDontSee('Dr. Twenty');

        $exportCsv = $this->get(route('lecturers.export', ['batch' => ['PPTI18', 'PPTI20']]))->streamedContent();
        $this->assertStringContainsString('Dr. Overlap', $exportCsv);
        $this->assertStringContainsString('Dr. Twenty', $exportCsv);
        $this->assertStringNotContainsString('Dr. Nineteen', $exportCsv);
    }
}
