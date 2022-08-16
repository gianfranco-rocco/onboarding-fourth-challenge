<select 
    id="{{ $id }}"
    name="{{ $name }}"
    onchange="{{ $onchange }}"
    {{ $attributes->merge(['class' => 'text-base focus:ring-4 focus:outline-none focus:ring-purple-300 rounded-lg py-2 text-center inline-flex items-center dark:focus:ring-purple-800']) }}
>
    {{ $slot }}
</select>