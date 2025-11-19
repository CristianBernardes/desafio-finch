<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * UserService instance.
     *
     * @var UserService
     */
    protected $userService;

    /**
     * AuthService constructor.
     *
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Attempt to authenticate a user.
     *
     * @param array $credentials
     * @return string|null
     */
    public function attempt(array $credentials): ?string
    {
        if (!$token = auth()->attempt($credentials)) {
            return null;
        }

        return $token;
    }

    /**
     * Register a new user.
     *
     * @param array $data
     * @return array
     */
    public function register(array $data): array
    {
        $user = $this->userService->createUser($data);
        $token = auth()->login($user);

        return [
            'user' => $user->load('profiles'),
            'token' => $token,
        ];
    }

    /**
     * Get authenticated user with profiles.
     *
     * @return User
     */
    public function me(): User
    {
        return auth()->user()->load('profiles');
    }

    /**
     * Logout user (invalidate token).
     *
     * @return void
     */
    public function logout(): void
    {
        auth()->logout();
    }

    /**
     * Refresh authentication token.
     *
     * @return string
     */
    public function refresh(): string
    {
        return auth()->refresh();
    }

    /**
     * Get token TTL in seconds.
     *
     * @return int
     */
    public function getTokenTTL(): int
    {
        return auth()->factory()->getTTL() * 60;
    }
}
