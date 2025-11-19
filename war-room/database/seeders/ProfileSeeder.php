<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Profile;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $profiles = [
            [
                'slug' => 'admin',
                'name' => 'Administrador',
                'is_global' => true,
            ],
            [
                'slug' => 'operator',
                'name' => 'Operador',
                'is_global' => false,
            ],
            [
                'slug' => 'viewer',
                'name' => 'Visualizador',
                'is_global' => false,
            ],
        ];

        foreach ($profiles as $profile) {
            Profile::firstOrCreate(
                ['slug' => $profile['slug']],
                $profile
            );
        }
    }
}
