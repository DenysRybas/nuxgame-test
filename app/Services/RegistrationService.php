<?php

namespace App\Services;

use App\Models\Link;
use App\Models\User;

class RegistrationService
{
    /**
     * Create a new user along with their first active link.
     */
    public function register(string $username, string $phoneNumber): Link
    {
        $user = User::create([
            'username' => $username,
            'phone_number' => $phoneNumber,
        ]);

        $link = app(LinkService::class)->issueFor($user->id);
        $link->setRelation('user', $user);

        return $link;
    }
}
