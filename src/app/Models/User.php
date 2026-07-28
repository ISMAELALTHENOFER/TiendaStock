<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'ADMIN';

    public const ROLE_VENTAS = 'Ventas';

    public const ROLE_CONTROL_STOCK = 'Control Stock';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isVentas(): bool
    {
        return $this->role === self::ROLE_VENTAS;
    }

    public function isControlStock(): bool
    {
        return $this->role === self::ROLE_CONTROL_STOCK;
    }

    public function hasRole(string|array $roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];

        return in_array($this->role, $roles, true);
    }

    /**
     * Check if user's role is at least the given level.
     * Hierarchy: ADMIN >= Control Stock >= Ventas
     */
    public function isAtLeast(string $role): bool
    {
        $hierarchy = [
            self::ROLE_ADMIN => 3,
            self::ROLE_CONTROL_STOCK => 2,
            self::ROLE_VENTAS => 1,
        ];

        $userLevel = $hierarchy[$this->role] ?? 0;
        $requiredLevel = $hierarchy[$role] ?? 0;

        return $userLevel >= $requiredLevel;
    }

    /**
     * @return string[]
     */
    public static function availableRoles(): array
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_VENTAS,
            self::ROLE_CONTROL_STOCK,
        ];
    }
}
