@vite('resources/css/table.css')

<table id="{{ $id }}" class="table table-fixed border-spacing-2 border-collapse border border-slate-500 w-full mb-6">
    <thead>
        <tr>
            {{ $heading }}
        </tr>
    </thead>
    <tbody id="{{ $id }}Tbody"></tbody>
</table>

<div class="table-pagination">
    {{ $records->links() }}
</div>