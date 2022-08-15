<div id="{{ $name }}-container">
    <label for="{{ $name }}" id="{{ $name }}-label" class="block mb-2 text-sm font-medium">{{ $label }}</label>
    <input 
        type="{{ $inputType }}" 
        id="{{ $name }}" 
        name="{{ $name }}" 
        class="border text-sm rounded-lg block w-full p-2.5 dark:bg-red-100" 
        placeholder="{{ $placeholder }}"
        value="{{ $value }}"
    >
</div>