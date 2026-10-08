<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_groups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('course_record_id')
                ->constrained('course_records')
                ->restrictOnDelete();

            $table->string('class_code');

            $table->unique(['course_record_id', 'class_code']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_groups');
    }
};