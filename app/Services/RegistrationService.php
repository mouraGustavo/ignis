<?php

namespace App\Services;

use App\Exceptions\VagasEsgotadasException;
use App\Mail\InscricaoCanceladaMail;
use App\Mail\InscricaoConfirmadaMail;
use App\Mail\InscricaoRecebidaMail;
use App\Models\ContatoInscrito;
use App\Models\Evento;
use App\Models\EventoInscrito;
use App\Models\InformacaoSaude;
use App\Models\Inscrito;
use App\Models\ResponsavelInscrito;
use App\Models\StatusInscricao;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class RegistrationService
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
        private readonly VagasLimiteService $vagasLimiteService,
    ) {
    }

    /**
     * Cria uma nova inscricao completa (inscrito + contato + saude + responsavel se
     * menor de idade + vinculo com o evento), dentro de uma unica transacao.
     *
     * @param  array  $data  Dados ja validados (ver App\Livewire\Inscricao::rules()).
     */
    public function register(array $data): EventoInscrito
    {
        $evento = Evento::atual();

        if (! $evento) {
            throw new \RuntimeException('Nenhum evento ativo encontrado.');
        }

        if ($evento->atingiuLimiteVagas()) {
            throw new VagasEsgotadasException();
        }

        return DB::transaction(function () use ($data, $evento) {
            $inscrito = Inscrito::create([
                'nome' => $data['full_name'],
                'data_nascimento' => $data['birth_date'],
                'tamanho_camisa_id' => $data['shirt_size_id'],
                'termos_aceitos' => [
                    'term_truth' => true,
                    'term_participation' => true,
                    'term_emergency' => true,
                    'term_image' => true,
                    'term_rules' => true,
                ],
            ]);

            ContatoInscrito::create([
                'inscrito_id' => $inscrito->id,
                'email' => $data['email'],
                'telefone' => $data['phone'],
                'nome_emergencia' => $data['emergency_contact_name'] ?? null,
                'telefone_emergencia' => $data['emergency_contact_phone'] ?? null,
            ]);

            InformacaoSaude::create([
                'inscrito_id' => $inscrito->id,
                'restricoes_alimentares' => $data['food_restrictions'] ?? null,
                'alergias' => $data['allergies'] ?? null,
                'medicamentos' => $data['medications'] ?? null,
                'condicao_saude' => $data['health_conditions'] ?? null,
            ]);

            if ($inscrito->isMenorDeIdade()) {
                $documentoUrl = $this->fileUploadService->storePrivate(
                    $data['guardian_document'],
                    'responsaveis/documentos'
                );

                ResponsavelInscrito::create([
                    'inscrito_id' => $inscrito->id,
                    'nome' => $data['guardian_name'],
                    'telefone' => $data['guardian_phone'] ?? $data['phone'],
                    'documento_url' => $documentoUrl,
                    'autorizado' => true,
                ]);
            }

            $statusPendente = StatusInscricao::query()->where('nome', StatusInscricao::PENDENTE)->firstOrFail();

            $eventoInscrito = EventoInscrito::create([
                'evento_id' => $evento->id,
                'inscrito_id' => $inscrito->id,
                'status_id' => $statusPendente->id,
                'numero_inscricao' => $this->gerarNumeroInscricao($evento, $inscrito->id),
            ]);

            Mail::to($data['email'])->queue(new InscricaoRecebidaMail($eventoInscrito->load('inscrito', 'evento')));

            return $eventoInscrito->load(['inscrito.contato', 'inscrito.informacaoSaude', 'inscrito.responsavel', 'status', 'evento']);
        });
    }

    /**
     * Atualiza os dados de uma inscricao existente (autoatendimento via "Minha Inscricao"),
     * dentro do prazo permitido (ver App\Livewire\MinhaInscricao::canEdit()). Nao altera
     * e-mail (chave usada para localizar a inscricao) nem campos de pagamento/status.
     *
     * @param  array  $data  Dados ja validados (ver App\Livewire\MinhaInscricao::rules()).
     */
    public function atualizarDados(EventoInscrito $eventoInscrito, array $data): EventoInscrito
    {
        return DB::transaction(function () use ($eventoInscrito, $data) {
            $inscrito = $eventoInscrito->inscrito;

            $inscrito->update([
                'nome' => $data['full_name'],
                'data_nascimento' => $data['birth_date'],
                'tamanho_camisa_id' => $data['shirt_size_id'],
            ]);

            $inscrito->contato()->update([
                'telefone' => $data['phone'],
                'nome_emergencia' => $data['emergency_contact_name'] ?? null,
                'telefone_emergencia' => $data['emergency_contact_phone'] ?? null,
            ]);

            $inscrito->informacaoSaude()->update([
                'restricoes_alimentares' => $data['food_restrictions'] ?? null,
                'alergias' => $data['allergies'] ?? null,
                'medicamentos' => $data['medications'] ?? null,
                'condicao_saude' => $data['health_conditions'] ?? null,
            ]);

            if ($inscrito->fresh()->isMenorDeIdade()) {
                $documentoUrl = $data['guardian_document']
                    ? $this->fileUploadService->storePrivate($data['guardian_document'], 'responsaveis/documentos')
                    : $inscrito->responsavel?->documento_url;

                ResponsavelInscrito::updateOrCreate(
                    ['inscrito_id' => $inscrito->id],
                    [
                        'nome' => $data['guardian_name'],
                        'telefone' => $data['guardian_phone'] ?? $data['phone'],
                        'documento_url' => $documentoUrl,
                        'autorizado' => true,
                    ]
                );
            }

            return $eventoInscrito->fresh(['inscrito.contato', 'inscrito.informacaoSaude', 'inscrito.responsavel', 'status', 'evento']);
        });
    }

    /**
     * Marca a inscricao como confirmada apos validacao do pagamento e dispara os efeitos
     * colaterais: e-mail de confirmacao para o jovem e checagem de limite de vagas
     * (gera/envia o .xlsx para o coordenador se o limite foi atingido).
     */
    public function confirmarPagamento(EventoInscrito $eventoInscrito): EventoInscrito
    {
        $statusConfirmada = StatusInscricao::query()->where('nome', StatusInscricao::CONFIRMADA)->firstOrFail();

        $eventoInscrito->update([
            'status_id' => $statusConfirmada->id,
            'pagamento_confirmado' => true,
        ]);

        $eventoInscrito->load(['inscrito.contato', 'status', 'evento']);

        Mail::to($eventoInscrito->inscrito->contato->email)->queue(new InscricaoConfirmadaMail($eventoInscrito));

        $this->vagasLimiteService->verificarLimite($eventoInscrito->evento);

        return $eventoInscrito->fresh(['inscrito.contato', 'status', 'evento']);
    }

    /**
     * Marca a inscricao como cancelada e notifica o inscrito por e-mail.
     */
    public function cancelarInscricao(EventoInscrito $eventoInscrito): EventoInscrito
    {
        $statusCancelada = StatusInscricao::query()->where('nome', StatusInscricao::CANCELADA)->firstOrFail();

        $eventoInscrito->update(['status_id' => $statusCancelada->id]);

        $eventoInscrito->load(['inscrito.contato', 'status', 'evento']);

        Mail::to($eventoInscrito->inscrito->contato->email)->queue(new InscricaoCanceladaMail($eventoInscrito));

        return $eventoInscrito->fresh(['inscrito.contato', 'status', 'evento']);
    }

    private function gerarNumeroInscricao(Evento $evento, int $inscritoId): string
    {
        return 'INS' . str_pad((string) $inscritoId, 6, '0', STR_PAD_LEFT);
    }
}
