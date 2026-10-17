@props(['name', 'label' => null, 'type' => 'text', 'value' => null, 'hint' => null])
@php $id = $attributes->get('id', str_replace(['[', ']', '.'], '_', $name)); @endphp
<div {{ $attributes->only('class') }}>
    @if ($label)
        <label for="{{ $id }}" class="label">{{ $label }} @if ($attributes->has('required'))<span class="text-red-500">*</span>@endif</label>
    @endif
    <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}"
        value="{{ $type === 'password' ? '' : old($name, $value) }}"
        {{ $attributes->except(['class', 'id'])->merge(['class' => 'input'.($errors->has($name) ? ' input-error' : '')]) }}>
    @if ($hint)<p class="mt-1 text-xs text-slate-400">{{ $hint }}</p>@endif
    @error($name)<p class="error">{{ $message }}</p>@enderror
</div>
