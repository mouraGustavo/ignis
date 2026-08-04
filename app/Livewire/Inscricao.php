<?php

namespace App\Livewire;

use App\Exceptions\VagasEsgotadasException;
use App\Models\Evento;
use App\Models\Setting;
use App\Models\TamanhoCamisa;
use App\Services\RecaptchaService;
use App\Services\RegistrationService;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.guest')]
class Inscricao extends Component
{
    use WithFileUploads;

    public const SHIRT_SIZES = ['PP', 'P', 'M', 'G', 'GG', 'XGG'];

    // Dados pessoais
    public string $full_name = '';

    public string $phone = '';

    public string $birth_date = '';

    public string $email = '';

    public string $shirt_size = '';

    // Informacoes medicas
    public string $food_restrictions = '';

    public string $allergies = '';

    public string $medications = '';

    public string $health_conditions = '';

    public string $emergency_contact_name = '';

    public string $emergency_contact_phone = '';

    // Responsavel (menor de idade)
    public string $guardian_name = '';

    public string $guardian_phone = '';

    public $guardianDocument = null;

    // Termos
    public bool $term_truth = false;

    public bool $term_participation = false;

    public bool $term_emergency = false;

    public bool $term_image = false;

    public bool $term_rules = false;

    public ?int $age = null;

    public bool $isFull = false;

    public int $minAge = 13;

    public ?string $formError = null;

    public function mount(): void
    {
        $evento = Evento::atual();

        $this->minAge = $evento?->idade_minima ?? 13;
        $this->isFull = ! $evento || $evento->atingiuLimiteVagas() || Setting::get('registration_open') === 'false';
    }

    /**
     * birth_date chega ao servidor via @entangle (deferred, sem .live) para o calculo de
     * idade no formulario ser instantaneo no navegador (ver resources/views/livewire/inscricao.blade.php)
     * — uma sincronizacao deferred atualiza a propriedade mas NAO dispara updatedBirthDate(),
     * entao a idade precisa ser recalculada aqui a partir de birth_date, nao de um hook.
     */
    private function calcularIdade(): void
    {
        $this->age = null;

        if ($this->birth_date) {
            try {
                $this->age = Carbon::parse($this->birth_date)->age;
            } catch (\Throwable) {
                $this->age = null;
            }
        }
    }

    public function isMenorDeIdade(): bool
    {
        return $this->age !== null && $this->age < 18;
    }

    protected function rules(): array
    {
        $isMenor = $this->isMenorDeIdade();

        return [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'birth_date' => ['required', 'date', 'before:today'],
            'email' => ['required', 'email', 'max:255', Rule::unique('contatos_inscrito', 'email')],
            'shirt_size' => ['required', Rule::in(self::SHIRT_SIZES)],

            'food_restrictions' => ['nullable', 'string', 'max:2000'],
            'allergies' => ['nullable', 'string', 'max:2000'],
            'medications' => ['nullable', 'string', 'max:2000'],
            'health_conditions' => ['nullable', 'string', 'max:2000'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],

            'guardian_name' => [$isMenor ? 'required' : 'nullable', 'string', 'max:255'],
            'guardian_phone' => ['nullable', 'string', 'max:20'],
            'guardianDocument' => [$isMenor ? 'required' : 'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],

            'term_truth' => ['accepted'],
            'term_participation' => ['accepted'],
            'term_emergency' => ['accepted'],
            'term_image' => ['accepted'],
            'term_rules' => ['accepted'],
        ];
    }

    protected function messages(): array
    {
        return [
            'email.unique' => 'Este e-mail já possui uma inscrição registrada.',
            'guardian_name.required' => 'O nome do responsável é obrigatório para menores de 18 anos.',
            'guardianDocument.required' => 'O documento do responsável é obrigatório para menores de 18 anos.',
            'term_truth.accepted' => 'É necessário declarar que as informações prestadas são verdadeiras.',
            'term_participation.accepted' => 'É necessário autorizar a participação no acampamento.',
            'term_emergency.accepted' => 'É necessário autorizar o atendimento de emergência.',
            'term_image.accepted' => 'É necessário autorizar o uso de imagem.',
            'term_rules.accepted' => 'É necessário se comprometer a cumprir as normas do acampamento.',
        ];
    }

    public function submit(RegistrationService $registrationService, RecaptchaService $recaptchaService, ?string $recaptchaToken = null): void
    {
        $this->formError = null;

        if (! $recaptchaService->verify($recaptchaToken, 'inscricao')) {
            $this->formError = 'Não foi possível validar sua solicitação automaticamente. Se estiver usando algum bloqueador de anúncios, desative-o para este site e tente novamente.';

            return;
        }

        $this->calcularIdade();
        $this->validate();

        if ($this->age === null || $this->age < $this->minAge) {
            $this->addError('birth_date', "A idade mínima para participação é de {$this->minAge} anos.");

            return;
        }

        $shirtSizeId = TamanhoCamisa::query()->where('tamanho', $this->shirt_size)->value('id');

        try {
            $eventoInscrito = $registrationService->register([
                'full_name' => $this->full_name,
                'birth_date' => $this->birth_date,
                'shirt_size_id' => $shirtSizeId,
                'email' => $this->email,
                'phone' => $this->phone,
                'emergency_contact_name' => $this->emergency_contact_name ?: null,
                'emergency_contact_phone' => $this->emergency_contact_phone ?: null,
                'food_restrictions' => $this->food_restrictions ?: null,
                'allergies' => $this->allergies ?: null,
                'medications' => $this->medications ?: null,
                'health_conditions' => $this->health_conditions ?: null,
                'guardian_name' => $this->guardian_name ?: null,
                'guardian_phone' => $this->guardian_phone ?: null,
                'guardian_document' => $this->guardianDocument,
            ]);
        } catch (VagasEsgotadasException) {
            $this->isFull = true;

            return;
        } catch (\RuntimeException $e) {
            $this->formError = $e->getMessage();

            return;
        }

        $this->redirectRoute('pagamento', ['eventoInscrito' => $eventoInscrito->id], navigate: false);
    }

    public function render()
    {
        return view('livewire.inscricao');
    }
}
