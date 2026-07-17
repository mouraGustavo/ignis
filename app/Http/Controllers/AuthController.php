<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Login e "me" nao existem mais como acoes de controller: a autenticacao
     * do painel admin e feita inteiramente pelo componente App\Livewire\Auth\Login
     * (guard "web", sessao), que chama Auth::attempt() diretamente.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
