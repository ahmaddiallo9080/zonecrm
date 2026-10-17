@props(['label', 'value', 'icon' => 'chart', 'tone' => 'primary', 'hint' => null, 'href' => null])
@php
    $tones = [
        'primary' => 'bg-primary-50 text-primary-600',
        'emerald' => 'bg-emerald-50 text-emerald-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'red' => 'bg-red-50 text-red-600',
        'violet' => 'bg-violet-50 text-violet-600',
    ];
    $tag = $href ? 'a' : 'div';
@endphp
<{{ $tag }} @if ($href) href="{{ $href }}" @endif class="card group flex items-start gap-4 p-5 {{ $href ? 'hover:ring-primary-300 transition' : '' }}">
    <div class="flex size-11 shrink-0 items-center justify-center rounded-xl {{ $tones[$tone] ?? $tones['primary'] }}">
        <x-icon :name="$icon" class="size-5.5" />
    </div>
    <div class="min-w-0">
        <p class="text-xs font-semibold text-slate-500">{{ $label }}</p>
        <p class="num mt-1 truncate text-xl font-extrabold tracking-tight text-ink" title="{{ $value }}">{{ $value }}</p>
        @if ($hint)<p class="mt-0.5 text-xs text-slate-400">{{ $hint }}</p>@endif
    </div>
</{{ $tag }}>
