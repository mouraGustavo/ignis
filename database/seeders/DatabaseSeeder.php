<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            StatusInscricaoSeeder::class,
            TamanhoCamisaSeeder::class,
            EventoSeeder::class,
            SettingSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
