<?php

namespace Tests\Feature\Routines;

use App\Routines\Models\Routine;
use App\Users\Enums\ProgressionStyle;
use App\Users\Enums\ProgressiveMidBlock;
use App\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProgressionStyleBackfillTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function backfill_sql_copies_user_training_defaults_onto_existing_routines(): void
    {
        $user = User::factory()->create([
            'progression_style_default' => ProgressionStyle::ProgressiveOverload,
            'progressive_mid_block_default' => ProgressiveMidBlock::Auto,
        ]);
        $routine = Routine::factory()->create([
            'user_id' => $user->id,
            'progression_style' => ProgressionStyle::StraightSets,
            'progressive_mid_block' => ProgressiveMidBlock::Ask,
        ]);

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
        } else {
            DB::statement(<<<'SQL'
                UPDATE routines
                INNER JOIN users ON users.id = routines.user_id
                SET
                    routines.progression_style = COALESCE(users.progression_style_default, 'straight_sets'),
                    routines.progressive_mid_block = COALESCE(users.progressive_mid_block_default, 'ask')
            SQL);
        }

        $routine->refresh();
        $this->assertSame(ProgressionStyle::ProgressiveOverload, $routine->progression_style);
        $this->assertSame(ProgressiveMidBlock::Auto, $routine->progressive_mid_block);
    }
}
