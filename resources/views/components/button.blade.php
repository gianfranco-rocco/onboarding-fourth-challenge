<button
    id="{{ $id }}"
    type="{{ $type }}"
    onclick="{{ $onclick }}"
    {{ $attributes->merge(['class' => 'hover:bg-blue-700 border rounded-lg px-5 py-2 hover:text-white bg-white']) }}
>
    {{ $slot }}
</button>