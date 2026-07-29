<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('routines', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        /** @var array<int, list<string>> $usedByUser */
        $usedByUser = [];

        DB::table('routines')
            ->orderBy('id')
            ->get(['id', 'user_id', 'name'])
            ->each(function (object $routine) use (&$usedByUser): void {
                $userId = (int) $routine->user_id;
                $usedByUser[$userId] ??= [];

                $base = Str::slug((string) $routine->name) ?: 'routine';
                $slug = $base;
                $suffix = 2;

                while (in_array($slug, $usedByUser[$userId], true)) {
                    $slug = $base.'-'.$suffix;
                    $suffix++;
                }

                $usedByUser[$userId][] = $slug;

                DB::table('routines')->where('id', $routine->id)->update(['slug' => $slug]);
            });

        Schema::table('routines', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
            $table->unique(['user_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::table('routines', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'slug']);
            $table->dropColumn('slug');
        });
    }
};
