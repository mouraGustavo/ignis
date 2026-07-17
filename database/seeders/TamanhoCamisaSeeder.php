<?php

namespace Database\Seeders;

use App\Models\TamanhoCamisa;
use Illuminate\Database\Seeder;

class TamanhoCamisaSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['PP', 'P', 'M', 'G', 'GG', 'XGG'] as $tamanho) {
            TamanhoCamisa::query()->updateOrCreate(['tamanho' => $tamanho]);
        }
    }
}
