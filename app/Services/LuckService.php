<?php

namespace App\Services;

use App\Enums\LuckResult;
use App\Models\LuckAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class LuckService
{
    public const int HISTORY_LIMIT = 3;

    public function generate(User $user): LuckAttempt
    {
        $number = random_int(1, 1000);
        $result = LuckResult::fromNumber($number);

        return LuckAttempt::create([
            'user_id' => $user->id,
            'number' => $number,
            'result' => $result,
            'prize' => $result === LuckResult::Win
                ? app(LuckPrizeCalculator::class)->calculate($number)
                : 0.0,
        ]);
    }

    /**
     * Get the user's last 3 lucky number generations.
     *
     * @return Collection<int, LuckAttempt>
     */
    public function getLatestHistory(User $user): Collection
    {
        return LuckAttempt::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->limit(self::HISTORY_LIMIT)
            ->get();
    }
}
