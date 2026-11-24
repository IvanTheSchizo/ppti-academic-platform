<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lecturers', function (Blueprint $table) {
            $table->date('job_start_date')->nullable()->after('gender');
        });

        Schema::create('batch_lecturer', function (Blueprint $table) {
            $table->foreignId('batch_id')->constrained('batches')->cascadeOnDelete();
            $table->foreignId('lecturer_id')->constrained('lecturers')->cascadeOnDelete();
            $table->primary(['batch_id', 'lecturer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_lecturer');

        Schema::table('lecturers', function (Blueprint $table) {
            $table->dropColumn('job_start_date');
        });
    }
};
