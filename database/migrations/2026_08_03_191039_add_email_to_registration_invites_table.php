<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registration_invites', function (Blueprint $table) {
            $table->string('email')->nullable()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('registration_invites', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
};
