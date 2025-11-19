<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'profile_slug' => 'admin',
            ],
            [
                'name' => 'Operator User',
                'email' => 'operator@example.com',
                'password' => Hash::make('password'),
                'profile_slug' => 'operator',
            ],
            [
                'name' => 'Viewer User',
                'email' => 'viewer@example.com',
                'password' => Hash::make('password'),
                'profile_slug' => 'viewer',
            ],
            [
                'name' => 'Multi Profile User',
                'email' => 'multi@example.com',
                'password' => Hash::make('password'),
                'profile_slugs' => ['viewer', 'operator']
            ],
        ];

        foreach ($users as $userData) {
            // Verifica se é perfil único ou múltiplo
            $profileSlugs = isset($userData['profile_slugs'])
                ? $userData['profile_slugs']
                : [$userData['profile_slug']];

            unset($userData['profile_slug'], $userData['profile_slugs']);

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            // Busca os perfis e associa ao usuário
            foreach ($profileSlugs as $slug) {
                $profile = Profile::where('slug', $slug)->first();
                if ($profile && !$user->profiles()->where('profile_id', $profile->id)->exists()) {
                    $user->profiles()->attach($profile->id);
                }
            }
        }
    }
}
