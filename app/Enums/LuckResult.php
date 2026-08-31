<?php

namespace App\Enums;

enum LuckResult: string
{
    case Win = 'win';
    case Lose = 'lose';

    public static function fromNumber(int $number): self
    {
        return $number % 2 === 0 ? self::Win : self::Lose;
    }

    public function label(): string
    {
        return match ($this) {
            self::Win => 'Win',
            self::Lose => 'Lose',
        };
    }
}
