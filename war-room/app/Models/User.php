<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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

    /*
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Relacionamento many-to-many com Profile.
     */
    public function profiles()
    {
        return $this->belongsToMany(Profile::class, 'profile_user')
            ->using(ProfileUser::class)
            ->withTimestamps();
    }

    /**
     * Verifica se o usuário tem determinado perfil.
     */
    public function hasProfile(string $slug): bool
    {
        return $this->profiles()->where('slug', $slug)->exists();
    }

    /**
     * Verifica se o usuário é admin (perfil global).
     */
    public function isAdmin(): bool
    {
        return $this->profiles()->where('slug', 'admin')->where('is_global', true)->exists();
    }
}
