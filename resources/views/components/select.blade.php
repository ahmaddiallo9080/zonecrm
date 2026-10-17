@props(['name', 'label' => null, 'options' => [], 'value' => null, 'placeholder' => null])
@php
    $id = $attributes->get('id', $name);
    $current = (string) old($name, $value);
@endphp
<div {{ $attributes->only('class') }}>
    @if ($label)
        <label for="{{ $id }}" class="label">{{ $label }} @if ($attributes->has('required'))<span class="text-red-500">*</span>@endif</label>
    @endif
    <select id="{{ $id }}" name="{{ $name }}" {{ $attributes->except(['class', 'id'])->merge(['class' => 'input'.($errors->has($name) ? ' input-error' : '')]) }}>
        @if ($placeholder !== null)<option value="">{{ $placeholder }}</option>@endif
        @foreach ($options as $key => $text)
            <option value="{{ $key }}" @selected($current === (string) $key)>{{ is_array($text) ? $text[0] : $text }}</option>
        @endforeach
    </select>
    @error($name)<p class="error">{{ $message }}</p>@enderror
</div>
