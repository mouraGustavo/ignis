<x-mail::message>
# Inscrição confirmada!

Olá, {{ $eventoInscrito->inscrito->nome }}.

Seu pagamento foi confirmado e sua inscrição para o **{{ $eventoInscrito->evento->titulo }}**
está **confirmada**.

Número de inscrição: **{{ $eventoInscrito->numero_inscricao }}**

Entre no [nosso grupo do WhatsApp](https://chat.whatsapp.com/JyAGCqVuyAj3TiKZCku1AD) para receber informações e avisos sobre o evento.

Nos vemos lá!<br>
{{ config('app.name') }}
</x-mail::message>
