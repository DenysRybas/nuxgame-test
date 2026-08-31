<?php

namespace App\Services;

class LuckPrizeCalculator
{
    public function calculate(int $number): float
    {
        return match (true) {
            $number > 900 => round($number * 0.7, 2),
            $number > 600 => round($number * 0.5, 2),
            $number > 300 => round($number * 0.3, 2),
            default => round($number * 0.1, 2),
        };
    }
}
