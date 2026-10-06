<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\ClassGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ClassGroupSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        ClassGroup::truncate();
        Schema::enableForeignKeyConstraints();

        $batches = Batch::all();

        foreach ($batches as $batch) {
            preg_match('/\d+/', $batch->batch_name, $matches);
            $num = $matches[0] ?? $batch->id;

            foreach (['A', 'B'] as $section) {
                ClassGroup::create([
                    'class_code' => "{$num}{$section}",
                    'batch_id'   => $batch->id,
                ]);
            }
        }
    }
}