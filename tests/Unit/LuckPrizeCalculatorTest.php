<?php

namespace Tests\Unit;

use App\Services\LuckPrizeCalculator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LuckPrizeCalculatorTest extends TestCase
{
    #[Test]
    public function numbers_above_900_award_70_percent_of_the_number_as_a_prize(): void
    {
        $this->assertSame(665.0, (new LuckPrizeCalculator)->calculate(950));
    }

    #[Test]
    public function numbers_above_600_up_to_and_including_900_award_50_percent_of_the_number_as_a_prize(): void
    {
        $calculator = new LuckPrizeCalculator;

        $this->assertSame(450.0, $calculator->calculate(900));
        $this->assertSame(300.5, $calculator->calculate(601));
    }

    #[Test]
    public function numbers_above_300_up_to_and_including_600_award_30_percent_of_the_number_as_a_prize(): void
    {
        $calculator = new LuckPrizeCalculator;

        $this->assertSame(180.0, $calculator->calculate(600));
        $this->assertSame(90.3, $calculator->calculate(301));
    }

    #[Test]
    public function numbers_of_300_or_less_award_10_percent_of_the_number_as_a_prize(): void
    {
        $calculator = new LuckPrizeCalculator;

        $this->assertSame(30.0, $calculator->calculate(300));
        $this->assertSame(0.1, $calculator->calculate(1));
    }
}
