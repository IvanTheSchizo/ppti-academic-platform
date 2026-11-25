<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Lecturer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LecturerInactiveReasonTest extends TestCase
{
    use RefreshDatabase;

    public function test_input_data_form_and_lecturer_profile_support_inactive_reason(): void
    {
        $admin = Admin::create([
            'username' => 'admin_lecturer_reason',
            'password' => Hash::make('Password1!'),
        ]);
        $this->actingAs($admin);

        $this->post(route('input-data.store', 'lecturer'), [
            'lecturer_code' => 'D9501',
            'name' => 'Dr. Inactive',
            'status' => 'Inactive',
            'status_reason' => 'Sabbatical leave',
        ])->assertRedirect();

        $lecturer = Lecturer::where('lecturer_code', 'D9501')->firstOrFail();
        $this->assertSame('Inactive', $lecturer->status);
        $this->assertSame('Sabbatical leave', $lecturer->status_reason);

        $profileResponse = $this->get(route('lecturers.profile', $lecturer->id));
        $profileResponse->assertOk();
        $profileResponse->assertSee('Inactive');
        $profileResponse->assertSee('Sabbatical leave');

        $this->put(route('lecturers.update', $lecturer->id), [
            'lecturer_code' => 'D9501',
            'name' => 'Dr. Inactive',
            'status' => 'Inactive',
            'status_reason' => 'Retired',
        ])->assertRedirect();

        $lecturer->refresh();
        $this->assertSame('Retired', $lecturer->status_reason);

        $exportList = $this->get(route('lecturers.export'))->streamedContent();
        $this->assertStringContainsString('Reason', $exportList);
        $this->assertStringContainsString('Retired', $exportList);

        $exportProfile = $this->get(route('lecturers.profile.export', $lecturer->id))->streamedContent();
        $this->assertStringContainsString('Reason', $exportProfile);
        $this->assertStringContainsString('Retired', $exportProfile);

        // Switching back to Active clears status_reason
        $this->put(route('lecturers.update', $lecturer->id), [
            'lecturer_code' => 'D9501',
            'name' => 'Dr. Inactive',
            'status' => 'Active',
            'status_reason' => 'Should be cleared',
        ])->assertRedirect();

        $lecturer->refresh();
        $this->assertSame('Active', $lecturer->status);
        $this->assertNull($lecturer->status_reason);
    }

    public function test_lecturer_csv_import_supports_inactive_status_reason(): void
    {
        $admin = Admin::create([
            'username' => 'admin_lecturer_reason_csv',
            'password' => Hash::make('Password1!'),
        ]);
        $this->actingAs($admin);

        $csv = implode("\n", [
            'lecturer_code,name,status,status_reason',
            'D9601,Dr. CSV Inactive,Inactive,Study leave abroad',
        ]);

        $file = UploadedFile::fake()->createWithContent('lecturers_inactive.csv', $csv);
        $preview = $this->postJson(route('input-data.import.preview', 'lecturer'), ['file' => $file]);
        $preview->assertOk()->assertJsonPath('valid', 1)->assertJsonPath('invalid', 0);

        $this->postJson(route('input-data.import.commit', 'lecturer'), ['token' => $preview->json('token')])
            ->assertOk()
            ->assertJsonPath('imported', 1);

        $lecturer = Lecturer::where('lecturer_code', 'D9601')->firstOrFail();
        $this->assertSame('Inactive', $lecturer->status);
        $this->assertSame('Study leave abroad', $lecturer->status_reason);

        // Re-import with blank reason retains existing reason
        $reimportCsv = implode("\n", [
            'lecturer_code,name,status,status_reason',
            'D9601,Dr. CSV Inactive,Inactive,',
        ]);
        $reimportFile = UploadedFile::fake()->createWithContent('lecturers_inactive_reimport.csv', $reimportCsv);
        $reimportPreview = $this->postJson(route('input-data.import.preview', 'lecturer'), ['file' => $reimportFile]);
        $reimportPreview->assertOk()->assertJsonPath('valid', 1)->assertJsonPath('invalid', 0);

        $this->postJson(route('input-data.import.commit', 'lecturer'), ['token' => $reimportPreview->json('token')])
            ->assertOk()
            ->assertJsonPath('imported', 1);

        $lecturer->refresh();
        $this->assertSame('Study leave abroad', $lecturer->status_reason);

        // Re-import switching to Active clears status_reason
        $activeCsv = implode("\n", [
            'lecturer_code,name,status,status_reason',
            'D9601,Dr. CSV Inactive,Active,',
        ]);
        $activeFile = UploadedFile::fake()->createWithContent('lecturers_active_reimport.csv', $activeCsv);
        $activePreview = $this->postJson(route('input-data.import.preview', 'lecturer'), ['file' => $activeFile]);
        $activePreview->assertOk()->assertJsonPath('valid', 1)->assertJsonPath('invalid', 0);

        $this->postJson(route('input-data.import.commit', 'lecturer'), ['token' => $activePreview->json('token')])
            ->assertOk()
            ->assertJsonPath('imported', 1);

        $lecturer->refresh();
        $this->assertSame('Active', $lecturer->status);
        $this->assertNull($lecturer->status_reason);
    }
}
