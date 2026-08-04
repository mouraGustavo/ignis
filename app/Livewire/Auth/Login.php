<?php

namespace App\Livewire\Auth;

use App\Services\RecaptchaService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public ?string $error = null;

    protected function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function login(RecaptchaService $recaptchaService, ?string $recaptchaToken = null): void
    {
        $this->error = null;

        if (! $recaptchaService->verify($recaptchaToken, 'login')) {
            $this->error = 'Não foi possível validar sua solicitação automaticamente. Se estiver usando algum bloqueador de anúncios, desative-o para este site e tente novamente.';

            return;
        }

        $this->validate();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $this->error = 'As credenciais informadas estão incorretas.';

            return;
        }

        request()->session()->regenerate();

        $this->redirectRoute('admin.dashboard', navigate: false);
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
