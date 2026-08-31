<?php

namespace Tests\Feature;

use App\Enums\LuckResult;
use App\Models\Link;
use App\Models\LuckAttempt;
use App\Models\User;
use App\Services\LuckPrizeCalculator;
use App\Services\LuckService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LuckTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function anyone_holding_the_link_sees_page_a_without_logging_in(): void
    {
        $link = Link::factory()->for(User::factory())->create();

        $response = $this->get(route('luck', $link));

        $response->assertOk();
        $response->assertSee(route('luck', $link));
        $response->assertSee($link->user->username);
        $response->assertSee('Imfeelinglucky');
    }

    #[Test]
    public function an_unknown_token_is_not_found(): void
    {
        $this->get(route('luck', 'not-a-real-token'))->assertNotFound();
    }

    #[Test]
    public function a_deactivated_link_is_not_found(): void
    {
        $link = Link::factory()->inactive()->for(User::factory())->create();

        $this->get(route('luck', $link))->assertNotFound();
    }

    #[Test]
    public function a_link_older_than_7_days_is_not_found_even_before_the_cleanup_command_runs(): void
    {
        $link = Link::factory()->for(User::factory())->create();

        $this->travelTo(now()->addDays(Link::ACTIVE_DAYS)->addSecond());

        $this->get(route('luck', $link))->assertNotFound();
    }

    #[Test]
    public function a_link_is_still_reachable_just_under_7_days_old(): void
    {
        $link = Link::factory()->for(User::factory())->create();

        $this->travelTo(now()->addDays(Link::ACTIVE_DAYS)->subSecond());

        $this->get(route('luck', $link))->assertOk();
    }

    #[Test]
    public function generating_a_lucky_number_stores_an_attempt_with_a_matching_result_and_prize(): void
    {
        $link = Link::factory()->for(User::factory())->create();

        $response = $this->post(route('luck.generate', $link));

        $response->assertRedirect(route('luck', $link));

        $attempt = LuckAttempt::sole();
        $this->assertSame($link->user_id, $attempt->user_id);
        $this->assertGreaterThanOrEqual(1, $attempt->number);
        $this->assertLessThanOrEqual(1000, $attempt->number);
        $this->assertSame(LuckResult::fromNumber($attempt->number), $attempt->result);

        $expectedPrize = $attempt->result === LuckResult::Win
            ? (new LuckPrizeCalculator)->calculate($attempt->number)
            : 0.0;
        $this->assertSame($expectedPrize, $attempt->prize);
    }

    /**
     * One roll only exercises one branch, so roll enough times to hit both:
     * a loss must store a zero prize, a win the calculated one.
     */
    #[Test]
    public function losing_attempts_store_a_zero_prize_and_winning_ones_the_calculated_prize(): void
    {
        $user = User::factory()->create();
        $service = app(LuckService::class);
        $calculator = new LuckPrizeCalculator;

        for ($i = 0; $i < 50; $i++) {
            $service->generate($user);
        }

        $attempts = LuckAttempt::all();

        foreach ($attempts as $attempt) {
            $this->assertSame(
                $attempt->result === LuckResult::Win ? $calculator->calculate($attempt->number) : 0.0,
                $attempt->prize,
            );
        }

        $this->assertTrue($attempts->contains(fn (LuckAttempt $a): bool => $a->result === LuckResult::Win));
        $this->assertTrue($attempts->contains(fn (LuckAttempt $a): bool => $a->result === LuckResult::Lose));
    }

    #[Test]
    public function the_result_of_a_roll_is_shown_on_page_a(): void
    {
        $link = Link::factory()->for(User::factory())->create();

        $response = $this->followingRedirects()->post(route('luck.generate', $link));

        $attempt = LuckAttempt::sole();

        $response->assertOk();
        $response->assertSee((string) $attempt->number);
        $response->assertSee($attempt->result->label());
        $response->assertSee('Prize: '.$attempt->prize);
    }

    /**
     * Sessions are serialized as JSON, so anything flashed must survive a JSON
     * round trip. An Eloquent model would not: it returns as an untyped array
     * and the view fails. Tests share one process, so `followingRedirects()`
     * alone never exercises the serializer.
     */
    #[Test]
    public function the_flashed_result_survives_the_json_session_serializer(): void
    {
        $link = Link::factory()->for(User::factory())->create();

        $this->post(route('luck.generate', $link));

        $flashed = session('attempt');

        $this->assertSame('json', config('session.serialization'));
        $this->assertIsArray($flashed);
        // Loose comparison: json_encode turns a 0.0 prize into 0, which renders
        // the same. What matters is that no object is smuggled into the session.
        $this->assertEquals($flashed, json_decode(json_encode($flashed), associative: true));
    }

    #[Test]
    public function refreshing_page_a_after_a_roll_does_not_roll_again(): void
    {
        $link = Link::factory()->for(User::factory())->create();

        $this->followingRedirects()->post(route('luck.generate', $link));
        $this->get(route('luck', $link));

        $this->assertDatabaseCount('luck_attempts', 1);
    }

    #[Test]
    public function a_deactivated_link_can_no_longer_generate_a_lucky_number(): void
    {
        $link = Link::factory()->inactive()->for(User::factory())->create();

        $this->post(route('luck.generate', $link))->assertNotFound();
        $this->assertDatabaseCount('luck_attempts', 0);
    }

    #[Test]
    public function an_expired_link_can_no_longer_generate_a_lucky_number(): void
    {
        $link = Link::factory()->for(User::factory())->create();

        $this->travelTo(now()->addDays(Link::ACTIVE_DAYS)->addSecond());

        $this->post(route('luck.generate', $link))->assertNotFound();
        $this->assertDatabaseCount('luck_attempts', 0);
    }

    #[Test]
    public function history_returns_only_the_link_owners_last_3_attempts_newest_first(): void
    {
        $link = Link::factory()->for(User::factory())->create();
        LuckAttempt::factory()->for($link->user)->count(4)->sequence(fn ($sequence) => [
            'number' => 101 + $sequence->index,
            'created_at' => now()->subMinutes(10 - $sequence->index),
        ])->create();
        LuckAttempt::factory()->for(User::factory())->create(['number' => 999]); // another user's, must be excluded

        $response = $this->get(route('luck.history', $link));

        $response->assertOk();
        // Exactly three rows are rendered, so the oldest attempt and the other
        // user's attempt are both absent; assertSeeInOrder pins which three.
        $response->assertSee('Last 3 tries');
        $response->assertSeeInOrder(['104', '103', '102']);
    }

    #[Test]
    public function history_is_ordered_newest_first_even_when_attempts_share_a_timestamp(): void
    {
        $link = Link::factory()->for(User::factory())->create();
        LuckAttempt::factory()->for($link->user)->count(3)
            ->sequence(fn ($sequence) => ['number' => 201 + $sequence->index])
            ->create(['created_at' => now()]);

        $response = $this->get(route('luck.history', $link));

        $response->assertOk();
        $response->assertSeeInOrder(['203', '202', '201']);
    }

    #[Test]
    public function a_deactivated_link_can_no_longer_read_history(): void
    {
        $link = Link::factory()->inactive()->for(User::factory())->create();

        $this->get(route('luck.history', $link))->assertNotFound();
    }
}
