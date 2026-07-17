<x-mail::message>
# Limite de vagas atingido

O evento **{{ $evento->titulo }}** atingiu o limite de {{ $evento->limite_vagas }} inscrições
confirmadas. As novas inscrições foram bloqueadas automaticamente.

Segue em anexo a planilha (.xlsx) com nome e telefone/WhatsApp de todos os participantes
confirmados.

{{ config('app.name') }}
</x-mail::message>
