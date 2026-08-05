<?php

namespace Tests\Unit\Routines\Data\Editor;

use App\Routines\Data\Editor\SyncSetGroupData;
use App\Routines\Data\Editor\SyncWarmUpData;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SyncRestSecondsDataTest extends TestCase
{
    #[Test]
    public function blank_working_rest_seconds_become_zero(): void
    {
        $fromNull = SyncSetGroupData::validateAndCreate([
            'set_count' => 3,
            'rest_seconds' => null,
        ]);
        $fromEmpty = SyncSetGroupData::validateAndCreate([
            'set_count' => 3,
            'rest_seconds' => '',
        ]);

        $this->assertSame(0, $fromNull->restSeconds);
        $this->assertSame(0, $fromEmpty->restSeconds);
    }

    #[Test]
    public function blank_warm_up_rest_seconds_become_zero(): void
    {
        $fromNull = SyncWarmUpData::validateAndCreate([
            'set_count' => 0,
            'rest_seconds' => null,
            'steps' => [],
        ]);

        $this->assertSame(0, $fromNull->restSeconds);
    }
}
