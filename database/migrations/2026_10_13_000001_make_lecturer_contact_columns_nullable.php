<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lecturers', function (Blueprint $table) {
            $table->string('nip')->nullable()->change();
            $table->string('email_binus_edu')->nullable()->change();
            $table->string('email_binus_ac_id')->nullable()->change();
            $table->string('phone_number')->nullable()->change();
            $table->string('jja')->nullable()->change();
            $table->string('latest_education')->nullable()->change();
            $table->string('gender')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('lecturers', function (Blueprint $table) {
            $table->dropColumn('gender');
        });
    }
};
