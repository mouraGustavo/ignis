<?php

namespace App\View\Components;

use Illuminate\View\Component;

class FormularioCartaoCredito extends Component
{
    public function __construct(
        public int $valueCents = 15000,
        public string $payeeCode = '',
        public string $environment = 'sandbox',
        public int $maxInstallments = 2,
    ) {
    }

    public function render()
    {
        return view('components.formulario-cartao-credito');
    }
}
