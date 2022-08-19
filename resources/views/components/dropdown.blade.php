<select 
    id="{{ $id }}"
    name="{{ $name }}"
    onchange="{{ $onchange }}"
    {{ $attributes->merge(['class' => 'text-base focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg py-2 inline-flex items-center dark:focus:ring-blue-800']) }}
>
    {{ $slot }}
</select>