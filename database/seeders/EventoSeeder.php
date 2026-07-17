<?php

namespace Database\Seeders;

use App\Models\Evento;
use Illuminate\Database\Seeder;

class EventoSeeder extends Seeder
{
    /**
     * Evento ativo padrao, conforme especificacao funcional: limite de 150 vagas,
     * idade minima de 13 anos e valor de inscricao R$ 150,00.
     */
    public function run(): void
    {
        Evento::query()->updateOrCreate(
            ['titulo' => '5º Acampamento de Jovens Ignis'],
            [
                'descricao' => 'Um encontro de jovens para viver dias intensos de fé, oração, amizade e muita alegria.',
                'data_inicio' => '2026-08-02',
                'data_fim' => '2026-08-02',
                'local' => 'Paróquia São Pedro Apóstolo',
                'valor_inscricao' => 150.00,
                'limite_vagas' => 150,
                'idade_minima' => 13,
                'ativo' => true,
            ]
        );
    }
}
