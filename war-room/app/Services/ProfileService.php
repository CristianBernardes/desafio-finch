<?php

namespace App\Services;

use App\Models\Profile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class ProfileService extends BaseService
{
    /**
     * Get the model instance.
     *
     * @return Model
     */
    protected function model(): Model
    {
        return new Profile();
    }

    /**
     * Find profile by slug.
     *
     * @param string $slug
     * @return Profile|null
     */
    public function findBySlug(string $slug): ?Profile
    {
        return $this->findBy('slug', $slug);
    }

    /**
     * Get all global profiles.
     *
     * @return Collection
     */
    public function getGlobalProfiles(): Collection
    {
        return $this->getWhere(['is_global' => true]);
    }

    /**
     * Get all non-global profiles.
     *
     * @return Collection
     */
    public function getNonGlobalProfiles(): Collection
    {
        return $this->getWhere(['is_global' => false]);
    }

    /**
     * Get profile with users.
     *
     * @param int $id
     * @return Profile
     */
    public function getProfileWithUsers(int $id): Profile
    {
        return $this->findOrFail($id)->load('users');
    }
}
