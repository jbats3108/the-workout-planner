<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plate_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name')->default('Home gym');
            $table->timestamps();

            $table->unique(['user_id', 'name']);
        });

        Schema::create('plate_profile_bars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plate_profile_id')->constrained('plate_profiles')->cascadeOnDelete();
            $table->string('name'); // e.g. Olympic
            $table->unsignedInteger('weight_g'); // e.g. 20000
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('plate_profile_plates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plate_profile_id')->constrained('plate_profiles')->cascadeOnDelete();
            $table->unsignedInteger('denomination_g');
            $table->unsignedSmallInteger('count')->default(0);
            $table->string('colour', 32)->nullable();
            $table->timestamps();

            $table->unique(['plate_profile_id', 'denomination_g']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plate_profile_plates');
        Schema::dropIfExists('plate_profile_bars');
        Schema::dropIfExists('plate_profiles');
    }
};
