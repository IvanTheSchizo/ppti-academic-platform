<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('cumulative_gpa');
        });

        Schema::table('lecturers', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('gender');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('photo_path');
        });

        Schema::table('lecturers', function (Blueprint $table) {
            $table->dropColumn('photo_path');
        });
    }
};
