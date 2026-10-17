@props(['title', 'subtitle' => null, 'back' => null])
<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div class="flex items-start gap-3">
        @if ($back)
            <a href="{{ $back }}" class="btn-ghost mt-0.5 -ml-2" title="Retour"><x-icon name="arrow-left" class="size-4.5" /></a>
        @endif
        <div>
            <h1 class="page-title">{{ $title }}</h1>
            @if ($subtitle)<p class="page-subtitle">{!! $subtitle !!}</p>@endif
        </div>
    </div>
    @if ($slot->isNotEmpty())
        <div class="flex flex-wrap items-center gap-2">{{ $slot }}</div>
    @endif
</div>
