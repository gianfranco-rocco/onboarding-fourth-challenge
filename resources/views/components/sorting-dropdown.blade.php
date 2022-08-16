<button
    id="{{ $id }}"
    type="button"
    data-dropdown-toggle="{{ $id }}-dropdown"
    class="text-white purple-button focus:ring-4 focus:outline-none focus:ring-purple-300 font-medium rounded-lg text-base px-4 py-2.5 text-center inline-flex items-center dark:bg-purple-600 dark:hover:bg-purple-700 dark:focus:ring-purple-800" 
>
    {{ $label }} <svg class="ml-2 w-4 h-4" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
</button>

<div
    id="{{ $id }}-dropdown" 
    class="hidden z-10 w-44 bg-white rounded divide-y divide-gray-100 shadow dark:bg-gray-700">
    <ul class="py-1 text-base text-gray-700 dark:text-gray-200" aria-labelledby="{{ $id }}">
        {{ $slot }}
    </ul>
</div>