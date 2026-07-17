<x-mail::message>
# Inscrição recebida!

Olá, {{ $eventoInscrito->inscrito->nome }}.

Recebemos sua inscrição para o **{{ $eventoInscrito->evento->titulo }}**.

Número de inscrição: **{{ $eventoInscrito->numero_inscricao }}**

Sua inscrição está com status **pendente** até a confirmação do pagamento. Assim que
identificarmos o pagamento, você receberá um novo e-mail de confirmação.

Obrigado!<br>
{{ config('app.name') }}
</x-mail::message>
