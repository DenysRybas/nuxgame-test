<?php

namespace Tests\Feature;

use App\Enums\LinkStatus;
use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeactivateExpiredLinksTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function deactivates_active_links_older_than_7_days(): void
    {
        $user = User::factory()->create();

        $expired = Link::factory()->for($user)->create();

        $this->travelTo(now()->addDays(7)->addSecond());

        $this->artisan('app:deactivate-expired-links');

        $this->assertSame(LinkStatus::Inactive, $expired->fresh()->status);
    }

    #[Test]
    public function leaves_active_links_younger_than_7_days_untouched(): void
    {
        $user = User::factory()->create();

        $recent = Link::factory()->for($user)->create();

        $this->travelTo(now()->addDays(7)->subSecond());

        $this->artisan('app:deactivate-expired-links');

        $this->assertSame(LinkStatus::Active, $recent->fresh()->status);
    }

    #[Test]
    public function leaves_already_inactive_links_untouched(): void
    {
        $user = User::factory()->create();

        $inactive = Link::factory()->inactive()->for($user)->create();

        $this->travelTo(now()->addDays(30));

        $this->artisan('app:deactivate-expired-links');

        $this->assertSame(LinkStatus::Inactive, $inactive->fresh()->status);
    }
}
