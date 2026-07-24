<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Setting::DEFAULTS as $key => $value) {
            // firstOrCreate, nao updateOrCreate: rodar o seed de novo (ex.: `migrate --seed` num
            // ambiente ja em uso) nao pode resetar configuracoes que o admin ja alterou pelo painel.
            Setting::query()->firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
