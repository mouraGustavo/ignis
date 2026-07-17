<?php

namespace Database\Seeders;

use App\Models\StatusInscricao;
use Illuminate\Database\Seeder;

class StatusInscricaoSeeder extends Seeder
{
    public function run(): void
    {
        $status = [
            ['nome' => 'PENDENTE', 'descricao' => 'Inscrição realizada aguardando confirmação.', 'ordem' => 1],
            ['nome' => 'AGUARDANDO_PAGAMENTO', 'descricao' => 'Inscrição aguardando confirmação do pagamento.', 'ordem' => 2],
            ['nome' => 'CONFIRMADA', 'descricao' => 'Inscrição confirmada com pagamento aprovado.', 'ordem' => 3],
            ['nome' => 'CANCELADA', 'descricao' => 'Inscrição cancelada.', 'ordem' => 4],
            ['nome' => 'RECUSADA', 'descricao' => 'Inscrição recusada pela organização.', 'ordem' => 5],
            ['nome' => 'LISTA_ESPERA', 'descricao' => 'Inscrição aguardando vaga.', 'ordem' => 6],
        ];

        foreach ($status as $s) {
            StatusInscricao::query()->updateOrCreate(['nome' => $s['nome']], $s);
        }
    }
}
