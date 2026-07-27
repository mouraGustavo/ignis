<x-mail::message>
# Inscrição cancelada

Olá, {{ $eventoInscrito->inscrito->nome }}.

Sua inscrição para o **{{ $eventoInscrito->evento->titulo }}** foi **cancelada**.

Número de inscrição: **{{ $eventoInscrito->numero_inscricao }}**

Se você acredita que isso é um engano, entre em contato com a nossa organização.

{{ config('app.name') }}
</x-mail::message>
