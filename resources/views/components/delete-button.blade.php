@props(['action', 'confirm' => 'Confirmer la suppression ? Cette action est irréversible.', 'label' => null])
<form method="POST" action="{{ $action }}" onsubmit="return confirm(@js($confirm))" class="inline">
    @csrf @method('DELETE')
    <button type="submit" {{ $attributes->merge(['class' => $label ? 'btn-danger' : 'btn-ghost hover:!text-red-600']) }} title="Supprimer">
        <x-icon name="trash" class="size-4" />@if ($label)<span>{{ $label }}</span>@endif
    </button>
</form>
