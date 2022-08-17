<div id="{{ $inputId }}-container">
    <label for="{{ $inputId }}" id="{{ $inputId }}-label" class="block mb-2 text-sm font-medium">{{ $label }}</label>
    <x-input
        id="{{ $inputId }}"
        type="{{ $inputType }}"
        name="{{ $name }}"
        placeholder="{{ $placeholder }}"
        value="{{ $value }}"
        class="block w-full"
    />
</div>