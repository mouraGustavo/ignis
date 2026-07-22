<?php

namespace App\View\Components;

use Illuminate\View\Component;


class FormularioCartaoCredito extends Component
{
    public function __construct(
        public int $value = 150,
        public int $installments = 1
    ) {}

    public function render()
    {
        return view('components.formulario-cartao-credito');
    }
}