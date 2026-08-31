<?php

namespace App\Models;

use App\Enums\LinkStatus;
use Carbon\CarbonInterface;
use Database\Factories\LinkFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property LinkStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 *
 * @method static Builder<static>|Link active()
 * @method static Builder<static>|Link expired()
 * @method static Builder<static>|Link valid()
 */
#[Fillable(['user_id', 'token', 'status'])]
class Link extends Model
{
    /** @use HasFactory<LinkFactory> */
    use HasFactory;

    public const int ACTIVE_DAYS = 7;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => LinkStatus::class,
        ];
    }

    #[Scope]
    public function active(Builder $query): void
    {
        $query->where('status', LinkStatus::Active);
    }

    #[Scope]
    public function expired(Builder $query): void
    {
        $query->where('created_at', '<=', Carbon::now()->subDays(self::ACTIVE_DAYS));
    }

    /**
     * Links that still grant access: active and within the 7 day window.
     */
    #[Scope]
    public function valid(Builder $query): void
    {
        $query->active()->where('created_at', '>', Carbon::now()->subDays(self::ACTIVE_DAYS));
    }

    /**
     * The instance-side counterpart of the `valid` scope, used by EnsureLinkIsValid.
     */
    public function isValid(): bool
    {
        return $this->status === LinkStatus::Active
            && $this->expiresAt()?->isFuture();
    }

    public function expiresAt(): ?CarbonInterface
    {
        return $this->created_at?->addDays(self::ACTIVE_DAYS);
    }

    /**
     * Route the model by its token, so `route('luck', $link)` yields the unique link.
     */
    public function getRouteKeyName(): string
    {
        return 'token';
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
