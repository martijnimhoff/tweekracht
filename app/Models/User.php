<?php

declare(strict_types=1);

namespace App\Models;

use App\Mail\UserVerificationMail;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Class User
 * @package App\Models
 *
 * @property int $id
 * @property int $role_id
 * @property string $name
 * @property string $email
 * @property string $phone_number
 * @property string $password
 * @property float $rate
 * @property int $support_calculation
 * @property mixed $created_at
 * @property mixed $updated_at
 *
 * @property Role $role
 * @property Collection $companies
 * @property Collection $clients
 * @property Collection $reports
 */
final class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use Notifiable;
    use HasFactory;

    public const RATE_100 = 100.00;
    public const RATE_50 = 50.00;
    public const RATE_0 = 0.00;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'role_id',
        'name',
        'email',
        'password',
        'phone_number',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'role_id',
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'rate' => 'decimal:2',
    ];

    public function role(): BelongsTo
    {
        return $this->BelongsTo(Role::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    public function reports(): HasManyThrough
    {
        return $this->hasManyThrough(Report::class, Client::class);
    }

    public function reportsThisMonth(): HasManyThrough
    {
        return $this->reports()
            ->whereYear('reports.created_at', '=', now()->year)
            ->whereMonth('reports.created_at', '=', now()->month);
    }

    public function reportsPreviousMonth(): HasManyThrough
    {
        return $this->reports()
            ->whereYear('reports.created_at', '=', now()->subMonth()->year)
            ->whereMonth('reports.created_at', '=', now()->subMonth()->month);
    }

    public function reportsTwoMonthsAgo(): HasManyThrough
    {
        return $this->reports()
            ->whereYear('reports.created_at', '=', now()->subMonths(2)->year)
            ->whereMonth('reports.created_at', '=', now()->subMonths(2)->month);
    }

    /**
     * Send the email verification mail.
     *
     * @return void
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new UserVerificationMail());
    }

    /**
     * Check if an user has the admin role.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role_id === Role::ROLE_ADMIN;
    }

    public function getFormattedCreatedAtAttribute(): string
    {
        return $this->created_at->toFormattedDateString();
    }

    public function canAccessFilament(): bool
    {
        return $this->isAdmin();
    }
}
