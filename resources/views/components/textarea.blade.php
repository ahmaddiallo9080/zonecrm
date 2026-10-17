@props(['name', 'label' => null, 'value' => null, 'rows' => 3])
<div {{ $attributes->only('class') }}>
    @if ($label)<label for="{{ $name }}" class="label">{{ $label }}</label>@endif
    <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" {{ $attributes->except('class')->merge(['class' => 'input'.($errors->has($name) ? ' input-error' : '')]) }}>{{ old($name, $value) }}</textarea>
    @error($name)<p class="error">{{ $message }}</p>@enderror
</div>
