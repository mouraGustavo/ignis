@props(['status'])
@php
$map = [
    'CONFIRMADA' => ['label' => 'Confirmada', 'icon' => 'check', 'classes' => 'bg-green-500/20 text-green-400 border-green-500/30'],
    'PENDENTE' => ['label' => 'Pendente', 'icon' => 'clock', 'classes' => 'bg-amber-500/20 text-amber-400 border-amber-500/30'],
    'AGUARDANDO_PAGAMENTO' => ['label' => 'Aguardando pagamento', 'icon' => 'clock', 'classes' => 'bg-amber-500/20 text-amber-400 border-amber-500/30'],
    'LISTA_ESPERA' => ['label' => 'Lista de espera', 'icon' => 'clock', 'classes' => 'bg-amber-500/20 text-amber-400 border-amber-500/30'],
    'CANCELADA' => ['label' => 'Cancelada', 'icon' => 'x', 'classes' => 'bg-red-500/20 text-red-400 border-red-500/30'],
    'RECUSADA' => ['label' => 'Recusada', 'icon' => 'x', 'classes' => 'bg-red-500/20 text-red-400 border-red-500/30'],
];
$cfg = $map[$status] ?? ['label' => $status, 'icon' => 'clock', 'classes' => 'bg-slate-500/20 text-slate-400 border-slate-500/30'];
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 text-xs px-2 py-1 rounded-full border {$cfg['classes']}"]) }}>
    <x-icon :name="$cfg['icon']" class="w-3.5 h-3.5" />
    {{ $cfg['label'] }}
</span>
