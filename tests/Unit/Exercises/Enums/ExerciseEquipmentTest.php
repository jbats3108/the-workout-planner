<?php

namespace Tests\Unit\Exercises\Enums;

use App\Exercises\Enums\ExerciseEquipment;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExerciseEquipmentTest extends TestCase
{
    #[Test]
    public function barbell_and_ez_curl_bar_use_barbell_plates(): void
    {
        $this->assertTrue(ExerciseEquipment::Barbell->usesBarbellPlates());
        $this->assertTrue(ExerciseEquipment::EzCurlBar->usesBarbellPlates());
        $this->assertFalse(ExerciseEquipment::Dumbbell->usesBarbellPlates());
        $this->assertFalse(ExerciseEquipment::Machine->usesBarbellPlates());
    }
}
