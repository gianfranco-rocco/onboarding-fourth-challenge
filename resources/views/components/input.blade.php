<input 
    id="{{ $id }}"
    type="{{ $type }}"
    name="{{ $name }}"
    placeholder="{{ $placeholder }}"
    value="{{ $value }}"
    {{ $attributes->merge(['class' => 'border text-sm rounded-lg p-2.5 dark:bg-red-100']) }}
>