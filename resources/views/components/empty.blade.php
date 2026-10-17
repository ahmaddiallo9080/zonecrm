@props(['icon' => 'doc', 'title' => 'Aucun élément', 'text' => null])
<div class="flex flex-col items-center justify-center px-6 py-14 text-center">
    <div class="flex size-12 items-center justify-center rounded-2xl bg-primary-50 text-primary-500">
        <x-icon :name="$icon" class="size-6" />
    </div>
    <p class="mt-3 text-sm font-bold text-ink">{{ $title }}</p>
    @if ($text)<p class="mt-1 max-w-sm text-sm text-slate-500">{{ $text }}</p>@endif
    @if ($slot->isNotEmpty())<div class="mt-4">{{ $slot }}</div>@endif
</div>
