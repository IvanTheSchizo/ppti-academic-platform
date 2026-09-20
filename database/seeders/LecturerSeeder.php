<?php

namespace Database\Seeders;

use App\Models\Lecturer;
use Illuminate\Database\Seeder;

class LecturerSeeder extends Seeder
{
    public function run(): void
    {
        $lecturers = [
            [
                'nip' => '198701010001',
                'name' => 'Dr. Andi Pratama',
                'status' => 'Active',
            ],
            [
                'nip' => '198802020002',
                'name' => 'Dr. Budi Santoso',
                'status' => 'Active',
            ],
            [
                'nip' => '198903030003',
                'name' => 'Dr. Citra Maharani',
                'status' => 'Active',
            ],
            [
                'nip' => '199004040004',
                'name' => 'Dewi Lestari, M.Kom',
                'status' => 'Active',
            ],
            [
                'nip' => '199105050005',
                'name' => 'Eko Saputra, M.Kom',
                'status' => 'Inactive',
            ],
            [
                'nip' => '199206060006',
                'name' => 'Farah Nabila, M.Kom',
                'status' => 'Active',
            ],
        ];

        foreach ($lecturers as $lecturer) {
            Lecturer::create($lecturer);
        }
    }
}