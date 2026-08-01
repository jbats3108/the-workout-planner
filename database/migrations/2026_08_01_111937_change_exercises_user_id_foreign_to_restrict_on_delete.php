<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exercises', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
        });

        Schema::table('exercises', function (Blueprint $table): void {
            // Reject user delete while customs remain (User::deleting force-deletes them first).
            // nullOnDelete previously promoted customs into the shared catalog.
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('exercises', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
        });

        Schema::table('exercises', function (Blueprint $table): void {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }
};
