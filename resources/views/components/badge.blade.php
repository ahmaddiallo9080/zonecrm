@props(['color' => 'gray'])
@php
    $map = [
        'gray' => 'bg-slate-100 text-slate-600 ring-slate-500/15',
        'slate' => 'bg-slate-800 text-white ring-slate-900/10',
        'blue' => 'bg-primary-50 text-primary-700 ring-primary-600/20',
        'emerald' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'amber' => 'bg-amber-50 text-amber-700 ring-amber-600/25',
        'orange' => 'bg-orange-50 text-orange-700 ring-orange-600/25',
        'red' => 'bg-red-50 text-red-700 ring-red-600/20',
        'violet' => 'bg-violet-50 text-violet-700 ring-violet-600/20',
    ];
    $dot = [
        'gray' => 'bg-slate-400', 'slate' => 'bg-white', 'blue' => 'bg-primary-500', 'emerald' => 'bg-emerald-500',
        'amber' => 'bg-amber-500', 'orange' => 'bg-orange-500', 'red' => 'bg-red-500', 'violet' => 'bg-violet-500',
    ];
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 ring-inset whitespace-nowrap '.($map[$color] ?? $map['gray'])]) }}>
    <span class="size-1.5 rounded-full {{ $dot[$color] ?? $dot['gray'] }}"></span>{{ $slot }}
</span>
