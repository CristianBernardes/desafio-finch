<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'is_global',
    ];

    protected $casts = [
        'is_global' => 'boolean',
    ];

    /**
     * Relacionamento many-to-many com User.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'profile_user')
            ->using(ProfileUser::class)
            ->withTimestamps();
    }

    /**
     * Busca um perfil pelo slug.
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    /**
     * Retorna todos os perfis globais (admin).
     */
    public static function globals()
    {
        return static::where('is_global', true)->get();
    }
}
