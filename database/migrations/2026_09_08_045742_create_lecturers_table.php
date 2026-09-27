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
        Schema::create('lecturers', function (Blueprint $table) {
            $table->id();

            $table->string('nip')->unique();
            $table->string('lecturer_code')->unique();
            $table->string('name');

            $table->string('email_binus_edu')->unique();
            $table->string('email_binus_ac_id')->unique();

            $table->string('phone_number');
            $table->string('jja');
            $table->string('latest_education');

            $table->string('status');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lecturers');
    }
};
