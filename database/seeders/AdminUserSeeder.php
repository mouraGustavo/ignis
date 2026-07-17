<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => env('ADMIN_SEED_EMAIL', 'admin@ignis.local')],
            [
                'name' => 'Administrador Ignis',
                // Troque a senha padrao antes de subir para producao. Defina
                // ADMIN_SEED_PASSWORD no .env para nao deixar hardcoded.
                'password' => Hash::make(env('ADMIN_SEED_PASSWORD', 'password')),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
