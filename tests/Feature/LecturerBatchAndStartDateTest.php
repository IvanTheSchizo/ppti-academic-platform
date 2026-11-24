<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Batch;
use App\Models\Lecturer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LecturerBatchAndStartDateTest extends TestCase
{
    use RefreshDatabase;

    public function test_lecturer_list_displays_and_filters_by_batch(): void
    {
        $admin = Admin::create([
            'username' => 'admin_lbatch',
            'password' => Hash::make('Password1!'),
        ]);
        $this->actingAs($admin);

        $batch19 = Batch::create(['batch_name' => 'PPTI19']);
        $batch20 = Batch::create(['batch_name' => 'PPTI20']);

        $lecturer1 = Lecturer::create([
            'lecturer_code' => 'D5001',
            'name' => 'Dr. Multi Batch',
            'status' => 'Active',
            'job_start_date' => '2024-02-15',
        ]);
        $lecturer1->batches()->sync([$batch19->id, $batch20->id]);

        $lecturer2 = Lecturer::create([
            'lecturer_code' => 'D5002',
            'name' => 'Dr. Single Batch',
            'status' => 'Active',
            'job_start_date' => '2025-01-10',
        ]);
        $lecturer2->batches()->sync([$batch19->id]);

        $response = $this->get(route('lecturers.index'));
        $response->assertOk();
        $response->assertSee('PPTI19, PPTI20');
        $response->assertSee('Dr. Multi Batch');
        $response->assertSee('Dr. Single Batch');

        $filtered = $this->get(route('lecturers.index', ['batch' => 'PPTI20']));
        $filtered->assertOk();
        $filtered->assertSee('Dr. Multi Batch');
        $filtered->assertDontSee('Dr. Single Batch');
    }

    public function test_lecturer_profile_update_and_input_data_form_save_batches_and_job_start_date(): void
    {
        $admin = Admin::create([
            'username' => 'admin_lcrud',
            'password' => Hash::make('Password1!'),
        ]);
        $this->actingAs($admin);

        $batch18 = Batch::create(['batch_name' => 'PPTI18']);
        $batch19 = Batch::create(['batch_name' => 'PPTI19']);

        $this->post(route('input-data.store', 'lecturer'), [
            'lecturer_code' => 'D6001',
            'name' => 'Dr. Input Data',
            'status' => 'Active',
            'job_start_date' => '2023-09-01',
            'batch_ids' => [$batch18->id, $batch19->id],
        ])->assertRedirect();

        $lecturer = Lecturer::where('lecturer_code', 'D6001')->firstOrFail();
        $this->assertSame('2023-09-01', $lecturer->job_start_date?->toDateString());
        $this->assertSame(['PPTI18', 'PPTI19'], $lecturer->sortedBatchNames());
        $this->assertDatabaseHas('audit_logs', [
            'action_type' => 'CREATE',
            'target_entity' => 'Lecturer',
            'target_id' => $lecturer->id,
        ]);

        $profileResponse = $this->get(route('lecturers.profile', $lecturer->id));
        $profileResponse->assertOk();
        $profileResponse->assertSee('PPTI18, PPTI19');
        $profileResponse->assertSee('1 September 2023');

        $this->put(route('lecturers.update', $lecturer->id), [
            'lecturer_code' => 'D6001',
            'name' => 'Dr. Input Data',
            'status' => 'Active',
            'job_start_date' => '2024-03-20',
            'batch_ids' => [$batch19->id],
        ])->assertRedirect();

        $lecturer->refresh();
        $this->assertSame('2024-03-20', $lecturer->job_start_date?->toDateString());
        $this->assertSame(['PPTI19'], $lecturer->sortedBatchNames());
        $this->assertDatabaseHas('audit_logs', [
            'action_type' => 'UPDATE',
            'target_entity' => 'Lecturer',
            'target_id' => $lecturer->id,
        ]);

        $auditResponse = $this->get(route('audit-logs.index', ['target_entity' => 'Lecturer']));
        $auditResponse->assertOk();
        $auditResponse->assertSee('batches: PPTI18, PPTI19');
        $auditResponse->assertSee('batches: PPTI19');
        $auditResponse->assertSee('job_start_date: 2023-09-01');
        $auditResponse->assertSee('job_start_date: 2024-03-20');
        $auditResponse->assertDontSee('batches.0:');

        $exportList = $this->get(route('lecturers.export'))->streamedContent();
        $this->assertStringContainsString('Batches', $exportList);
        $this->assertStringContainsString('Job Start Date', $exportList);
        $this->assertStringContainsString('PPTI19', $exportList);
        $this->assertStringContainsString('2024-03-20', $exportList);

        $exportProfile = $this->get(route('lecturers.profile.export', $lecturer->id))->streamedContent();
        $this->assertStringContainsString('Batches', $exportProfile);
        $this->assertStringContainsString('Job Start Date', $exportProfile);
        $this->assertStringContainsString('PPTI19', $exportProfile);
        $this->assertStringContainsString('2024-03-20', $exportProfile);
    }
}
