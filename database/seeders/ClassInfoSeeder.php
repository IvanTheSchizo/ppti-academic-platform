<?php

namespace Database\Seeders;

use App\Models\ClassInfo;
use Illuminate\Database\Seeder;

class ClassInfoSeeder extends Seeder
{
    public function run(): void
    {
        $classes = [
            'Math 67',
            'PPTI 69',
            'Lost my 50/50',
        ];

        foreach ($classes as $className) {
            ClassInfo::create([
                'class_name' => $className,
            ]);
        }
    }
}