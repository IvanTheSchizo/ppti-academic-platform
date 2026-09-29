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
                'lecturer_code' => 'AAP',
                'name' => 'Dr. Andi Pratama',
                'email_binus_edu' => 'andi.pratama@binus.edu',
                'email_binus_ac_id' => 'andi.pratama@binus.ac.id',
                'phone_number' => '081234567801',
                'jja' => 'Lektor',
                'latest_education' => 'Doctoral',
                'status' => 'Active',
            ],
            [
                'nip' => '198802020002',
                'lecturer_code' => 'BDS',
                'name' => 'Dr. Budi Santoso',
                'email_binus_edu' => 'budi.santoso@binus.edu',
                'email_binus_ac_id' => 'budi.santoso@binus.ac.id',
                'phone_number' => '081234567802',
                'jja' => 'Lektor',
                'latest_education' => 'Doctoral',
                'status' => 'Active',
            ],
            [
                'nip' => '198903030003',
                'lecturer_code' => 'CMA',
                'name' => 'Dr. Citra Maharani',
                'email_binus_edu' => 'citra.maharani@binus.edu',
                'email_binus_ac_id' => 'citra.maharani@binus.ac.id',
                'phone_number' => '081234567803',
                'jja' => 'Asisten Ahli',
                'latest_education' => 'Doctoral',
                'status' => 'On Leave',
            ],
            [
                'nip' => '199004040004',
                'lecturer_code' => 'DLM',
                'name' => 'Dewi Lestari, M.Kom',
                'email_binus_edu' => 'dewi.lestari@binus.edu',
                'email_binus_ac_id' => 'dewi.lestari@binus.ac.id',
                'phone_number' => '081234567804',
                'jja' => 'Lektor',
                'latest_education' => 'Master',
                'status' => 'Inactive',
            ],
        ];

        foreach ($lecturers as $lecturer) {
            Lecturer::create($lecturer);
        }
    }
}