<?php

namespace Database\Factories;

use App\Enums\LuckResult;
use App\Models\LuckAttempt;
use App\Models\User;
use App\Services\LuckPrizeCalculator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LuckAttempt>
 */
class LuckAttemptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $number = fake()->numberBetween(1, 1000);

        return [
            'user_id' => User::factory(),
            'number' => $number,
            'result' => LuckResult::fromNumber($number),
            'prize' => (new LuckPrizeCalculator)->calculate($number),
        ];
    }
}
