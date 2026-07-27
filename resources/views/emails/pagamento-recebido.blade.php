<x-mail::message>
# Pagamento recebido!

Olá, {{ $eventoInscrito->inscrito->nome }}.

Recebemos seu comprovante/dados de pagamento para o **{{ $eventoInscrito->evento->titulo }}**.

Número de inscrição: **{{ $eventoInscrito->numero_inscricao }}**

Sua inscrição **ainda não foi confirmada**. Nossa equipe vai conferir o pagamento e você
receberá um novo e-mail assim que sua inscrição for confirmada.

Obrigado!<br>
{{ config('app.name') }}
</x-mail::message>
