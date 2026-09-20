<?php

namespace Database\Seeders;

use App\Models\Batch;
use Illuminate\Database\Seeder;

class BatchSeeder extends Seeder
{
    public function run(): void
    {
        $batches = [
            '2023',
            '2024',
            '2025',
            '2026',
        ];

        foreach ($batches as $batchName) {
            Batch::create([
                'batch_name' => $batchName,
            ]);
        }
    }
}