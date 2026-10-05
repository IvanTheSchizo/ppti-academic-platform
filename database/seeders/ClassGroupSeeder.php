<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\ClassGroup;
use Illuminate\Database\Seeder;

class ClassGroupSeeder extends Seeder
{
    public function run(): void
    {
        $classes = [
            [
                'batch' => 'PPTI23',
                'class_code' => '13-A',
            ],
            [
                'batch' => 'PPTI23',
                'class_code' => '13-B',
            ],
            [
                'batch' => 'PPTI24',
                'class_code' => '14-A',
            ],
            [
                'batch' => 'PPTI24',
                'class_code' => '14-B',
            ],
        ];

        foreach ($classes as $class) {
            ClassGroup::create([
                'batch_id' => Batch::where('batch_name', $class['batch'])->firstOrFail()->id,
                'class_code' => $class['class_code'],
            ]);
        }
    }
}