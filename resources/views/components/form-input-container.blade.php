<div id="{{ $inputId }}-container">
    <label for="{{ $inputId }}" id="{{ $inputId }}-label" class="block mb-2 text-sm font-medium">{{ $label }}</label>
    <input 
        id="{{ $inputId }}" 
        type="{{ $inputType }}" 
        name="{{ $name }}" 
        class="border text-sm rounded-lg block w-full p-2.5 dark:bg-red-100" 
        placeholder="{{ $placeholder }}"
        value="{{ $value }}"
    >
</div>