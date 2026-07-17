@props(['label', 'for' => null])
<div>
    <label @if($for) for="{{ $for }}" @endif class="block text-sm font-medium text-slate-300 mb-2">{{ $label }}</label>
    {{ $slot }}
</div>
