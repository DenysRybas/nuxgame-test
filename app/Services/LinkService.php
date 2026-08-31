<?php

namespace App\Services;

use App\Enums\LinkStatus;
use App\Models\Link;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LinkService
{
    /**
     * Issue a fresh active link for the given user.
     */
    public function issueFor(int $userId): Link
    {
        return Link::create([
            'user_id' => $userId,
            'token' => $this->generateUniqueToken(),
            'status' => LinkStatus::Active,
        ]);
    }

    /**
     * Deactivate the given link and issue a fresh one to the same user.
     */
    public function regenerate(Link $link): Link
    {
        return DB::transaction(function () use ($link): Link {
            $this->deactivate($link);

            return $this->issueFor($link->user_id);
        });
    }

    public function deactivate(Link $link): void
    {
        $link->update(['status' => LinkStatus::Inactive]);
    }

    private function generateUniqueToken(): string
    {
        do {
            $token = Str::random(32);
        } while (Link::query()->where('token', $token)->exists());

        return $token;
    }
}
