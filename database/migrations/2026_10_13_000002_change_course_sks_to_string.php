<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SCU is written as theory/practice (e.g. "2/4"), so it can no longer be an integer.
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('sks', 10)->change();
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->unsignedInteger('sks')->change();
        });
    }
};
