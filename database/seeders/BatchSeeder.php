<?php

namespace Database\Seeders;

use App\Models\Batch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class BatchSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Batch::truncate();
        Schema::enableForeignKeyConstraints();

        for ($i = 1; $i <= 25; $i++) {
            Batch::create([
                'batch_name' => "PPTI {$i}",
            ]);
        }
    }
}