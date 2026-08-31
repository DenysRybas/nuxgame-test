<?php

namespace App\Console\Commands;

use App\Enums\LinkStatus;
use App\Models\Link;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:deactivate-expired-links')]
#[Description('Deactivate links that have been active for more than 7 days')]
class DeactivateExpiredLinks extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $count = Link::query()
            ->active()
            ->expired()
            ->update(['status' => LinkStatus::Inactive]);

        $this->info("Deactivated {$count} expired link(s).");
    }
}
