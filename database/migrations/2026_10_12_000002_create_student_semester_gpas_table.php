<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_semester_gpas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            $table->foreignId('period_id')
                ->constrained('periods')
                ->restrictOnDelete();

            $table->decimal('gpa', 3, 2);

            $table->unique(['student_id', 'period_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_semester_gpas');
    }
};
