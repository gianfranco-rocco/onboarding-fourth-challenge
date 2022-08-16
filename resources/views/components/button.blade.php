<button
    id="{{ $id }}"
    type="{{ $type }}"
    onclick="{{ $onclick }}"
    {{ $attributes->merge(['class' => 'button border rounded-lg px-5 py-2 hover:text-white bg-white']) }}
>
    {{ $slot }}
</button>