<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class UserService extends BaseService
{
    /**
     * Get the model instance.
     *
     * @return Model
     */
    protected function model(): Model
    {
        return new User();
    }

    /**
     * Create a new user with hashed password.
     *
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User
    {
        $data['password'] = Hash::make($data['password']);

        return $this->create($data);
    }

    /**
     * Update user with optional password hashing.
     *
     * @param int $id
     * @param array $data
     * @return User
     */
    public function updateUser(int $id, array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->update($id, $data);
    }

    /**
     * Find user by email.
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return $this->findBy('email', $email);
    }

    /**
     * Get user with profiles.
     *
     * @param int $id
     * @return User
     */
    public function getUserWithProfiles(int $id): User
    {
        return $this->findOrFail($id)->load('profiles');
    }

    /**
     * Attach profiles to user.
     *
     * @param int $userId
     * @param array $profileIds
     * @return User
     */
    public function attachProfiles(int $userId, array $profileIds): User
    {
        $user = $this->findOrFail($userId);
        $user->profiles()->syncWithoutDetaching($profileIds);

        return $user->fresh('profiles');
    }

    /**
     * Sync profiles for user (replace all).
     *
     * @param int $userId
     * @param array $profileIds
     * @return User
     */
    public function syncProfiles(int $userId, array $profileIds): User
    {
        $user = $this->findOrFail($userId);
        $user->profiles()->sync($profileIds);

        return $user->fresh('profiles');
    }

    /**
     * Detach profiles from user.
     *
     * @param int $userId
     * @param array $profileIds
     * @return User
     */
    public function detachProfiles(int $userId, array $profileIds = []): User
    {
        $user = $this->findOrFail($userId);

        if (empty($profileIds)) {
            $user->profiles()->detach();
        } else {
            $user->profiles()->detach($profileIds);
        }

        return $user->fresh('profiles');
    }
}
