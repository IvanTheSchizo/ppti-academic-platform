<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\AuditLog;
use Illuminate\Database\Seeder;

class AuditLogSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Admin::first();

        if (! $admin) {
            return;
        }

        $logs = [
            [
                'admin_id'      => $admin->id,
                'action_type'   => 'CREATE',
                'target_entity' => 'CourseRecord',
                'target_id'     => 1,
                'old_value'     => null,
                'new_value'     => json_encode(['record_code' => 'CR-2024-S1-001', 'class' => '13A']),
                'created_at'    => now()->subDays(2),
            ],
            [
                'admin_id'      => $admin->id,
                'action_type'   => 'UPDATE',
                'target_entity' => 'Lecturer',
                'target_id'     => 1,
                'old_value'     => json_encode(['status' => 'Inactive']),
                'new_value'     => json_encode(['status' => 'Active']),
                'created_at'    => now()->subDay(),
            ],
            [
                'admin_id'      => $admin->id,
                'action_type'   => 'DELETE',
                'target_entity' => 'Student',
                'target_id'     => 42,
                'old_value'     => json_encode(['nim' => '2601234567', 'name' => 'John Doe']),
                'new_value'     => null,
                'created_at'    => now(),
            ],
        ];

        foreach ($logs as $log) {
            AuditLog::create($log);
        }
    }
}