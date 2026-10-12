<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Students are enrolled in a course record before any grade exists.
     */
    public function up(): void
    {
        Schema::table('student_grades', function (Blueprint $table) {
            $table->decimal('numeric_grade', 3, 2)->nullable()->change();
            $table->string('letter_grade', 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('student_grades', function (Blueprint $table) {
            $table->decimal('numeric_grade', 3, 2)->nullable(false)->change();
            $table->string('letter_grade', 2)->nullable(false)->change();
        });
    }
};
