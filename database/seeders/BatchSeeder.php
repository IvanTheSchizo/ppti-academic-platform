<?php

namespace Database\Seeders;

use App\Models\Batch;
use Illuminate\Database\Seeder;

class BatchSeeder extends Seeder
{
    public function run(): void
    {
        $batches = [
        'PPTI23',
        'PPTI24',
        'PPTI25',
        'PPTI26',
    ];

        foreach ($batches as $batchName) {
            Batch::create([
                'batch_name' => $batchName,
            ]);
        }
    }
}