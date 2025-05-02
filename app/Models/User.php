<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable  implements FilamentUser, HasAvatar
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url;
    }
    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            if ($this->role === 'engineer') {
                return true;
            }
            return ($this->role !== 'company' && $this->role !== 'maincon');
        }

        return true;
    }
    use SoftDeletes;
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'disabled',
        'last_login_ip',
        'last_login_time',
        'company_id',
        'maincon_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function isAdmin()
    {
        return ($this->role === 'admin' || $this->role === 'engineer');
    }
    public function isEngineer()
    {
        return ($this->role === 'engineer');
    }
    public function isClient()
    {
        return ($this->role === 'company' || $this->role === 'maincon');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function maincon()
    {
        return $this->belongsTo(MainCon::class);
    }
    public function incident()
    {
        return $this->hasMany(Incident::class, 'created_by');
    }
}
