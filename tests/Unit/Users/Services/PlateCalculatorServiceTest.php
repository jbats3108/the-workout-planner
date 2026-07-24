<?php

namespace Tests\Unit\Users\Services;

use App\Users\Services\PlateCalculatorService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PlateCalculatorServiceTest extends TestCase
{
    private PlateCalculatorService $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new PlateCalculatorService;
    }

    #[Test]
    public function it_loads_an_exact_even_stack(): void
    {
        $plates = [
            ['denomination_g' => 20000, 'count' => 2],
            ['denomination_g' => 10000, 'count' => 2],
            ['denomination_g' => 5000, 'count' => 2],
        ];

        // 20kg bar + 20 + 10 per side = 80kg
        $result = $this->calculator->nearest(80000, 20000, $plates);

        $this->assertNotNull($result);
        $this->assertTrue($result['exact']);
        $this->assertSame(80000, $result['total_g']);
        $this->assertSame(
            [
                ['denomination_g' => 20000, 'count' => 1, 'colour' => null],
                ['denomination_g' => 10000, 'count' => 1, 'colour' => null],
            ],
            $result['per_side']
        );
    }

    #[Test]
    public function it_returns_nearest_when_target_is_not_loadable(): void
    {
        $plates = [
            ['denomination_g' => 2500, 'count' => 4],
        ];

        // 20kg bar + 2.5kg per side steps → 25kg, 30kg, …
        $result = $this->calculator->nearest(26250, 20000, $plates);

        $this->assertNotNull($result);
        $this->assertFalse($result['exact']);
        $this->assertSame(25000, $result['total_g']);
        $this->assertSame(-1250, $result['delta_g']);
    }

    #[Test]
    public function it_returns_bar_only_when_target_is_at_or_below_bar(): void
    {
        $result = $this->calculator->nearest(15000, 20000, [
            ['denomination_g' => 10000, 'count' => 4],
        ]);

        $this->assertNotNull($result);
        $this->assertSame(20000, $result['total_g']);
        $this->assertSame([], $result['per_side']);
    }

    #[Test]
    public function it_ignores_odd_single_plates_that_cannot_mirror(): void
    {
        $result = $this->calculator->nearest(40000, 20000, [
            ['denomination_g' => 10000, 'count' => 1],
        ]);

        $this->assertNotNull($result);
        $this->assertSame(20000, $result['total_g']);
        $this->assertSame([], $result['per_side']);
    }
}
