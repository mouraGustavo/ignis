<?php

namespace Tests\Concerns;

use App\Models\ContatoInscrito;
use App\Models\Evento;
use App\Models\EventoInscrito;
use App\Models\Inscrito;
use App\Models\StatusInscricao;
use App\Models\TamanhoCamisa;
use Database\Seeders\SettingSeeder;
use Database\Seeders\StatusInscricaoSeeder;
use Database\Seeders\TamanhoCamisaSeeder;

trait CreatesInscricaoFixtures
{
    protected function seedBaseData(): void
    {
        $this->seed([
            StatusInscricaoSeeder::class,
            TamanhoCamisaSeeder::class,
            SettingSeeder::class,
        ]);
    }

    protected function createEventoInscrito(): EventoInscrito
    {
        $evento = Evento::query()->create([
            'titulo' => 'Teste Ignis',
            'descricao' => 'Evento de teste',
            'data_inicio' => now()->addMonth(),
            'data_fim' => now()->addMonth(),
            'local' => 'Local teste',
            'valor_inscricao' => 150,
            'limite_vagas' => 150,
            'idade_minima' => 13,
            'ativo' => true,
        ]);

        $tamanho = TamanhoCamisa::query()->first()
            ?? TamanhoCamisa::query()->create(['tamanho' => 'M', 'ordem' => 1]);

        $inscrito = Inscrito::query()->create([
            'nome' => 'João da Silva',
            'data_nascimento' => now()->subYears(16),
            'tamanho_camisa_id' => $tamanho->id,
            'termos_aceitos' => ['term_truth' => true],
        ]);

        ContatoInscrito::query()->create([
            'inscrito_id' => $inscrito->id,
            'email' => 'joao@example.com',
            'telefone' => '11999999999',
        ]);

        $statusPendente = StatusInscricao::query()->where('nome', StatusInscricao::PENDENTE)->firstOrFail();

        return EventoInscrito::query()->create([
            'evento_id' => $evento->id,
            'inscrito_id' => $inscrito->id,
            'status_id' => $statusPendente->id,
            'numero_inscricao' => 'INS000001',
        ]);
    }
}
