<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            $table->string('nim')->unique();
            $table->string('name');

            $table->foreignId('batch_id')
                ->constrained('batches')
                ->restrictOnDelete();

            $table->string('status');

            $table->decimal('cumulative_gpa', 3, 2)
                ->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};