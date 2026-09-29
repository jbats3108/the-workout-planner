<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Copy each owner's Training progression defaults onto their existing routines.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            DB::statement(<<<'SQL'
                UPDATE routines
                SET
                    progression_style = COALESCE(
                        (SELECT users.progression_style_default FROM users WHERE users.id = routines.user_id),
                        'straight_sets'
                    ),
                    progressive_mid_block = COALESCE(
                        (SELECT users.progressive_mid_block_default FROM users WHERE users.id = routines.user_id),
                        'ask'
                    )
            SQL);

            return;
        }

        DB::statement(<<<'SQL'
            UPDATE routines
            INNER JOIN users ON users.id = routines.user_id
            SET
                routines.progression_style = COALESCE(users.progression_style_default, 'straight_sets'),
                routines.progressive_mid_block = COALESCE(users.progressive_mid_block_default, 'ask')
        SQL);
    }

    public function down(): void
    {
        DB::table('routines')->update([
            'progression_style' => 'straight_sets',
            'progressive_mid_block' => 'ask',
        ]);
    }
};
