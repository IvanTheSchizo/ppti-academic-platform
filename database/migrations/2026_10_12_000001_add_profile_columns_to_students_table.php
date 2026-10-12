<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('gender')->nullable()->after('status');
            $table->string('email')->nullable()->after('gender');
            $table->string('phone_number', 30)->nullable()->after('email');
            $table->date('date_of_birth')->nullable()->after('phone_number');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['gender', 'email', 'phone_number', 'date_of_birth']);
        });
    }
};
