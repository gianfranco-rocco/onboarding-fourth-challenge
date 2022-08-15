@extends('layout')

@section ('title')
    Cities
@endsection

@section('body')
    <x-button data-modal-toggle="newCityModal" class="mb-4">New city</x-button>

    <x-table id="citiesTable" :records="$cities">
        <x-slot:heading>
            <th>ID</th>
            <th>Name</th>
            <th>Incoming Flights</th>
            <th>Outgoing Flights</th>
            <th></th>
        </x-slot:heading>
    </x-table>

    <x-modal 
        id="newCityModal"
        title="New city"
        submitBtnLabel="Save" 
        submitBtnOnclick="saveCity(newCityModal.id)"
        closeBtnOnclick="clearForm(newCityForm.id)"
    >
        <form id="newCityForm">
            <x-form-input-container
                name="name"
                label="Name"
                placeholder="Montevideo"
            />
        </form>
    </x-modal>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            loadCitiesIntoTable('citiesTable', @json($cities));
        });

        const saveCity = (modalId) => {
            const formId = 'newCityForm';

            ajaxRequest('{{ route("cities.store") }}', formId, 'POST', function (response) {
                clearForm(formId);

                ajaxRequest('{{ route("cities.index") }}', null, 'GET', function (response) {
                    loadCitiesIntoTable('citiesTable', response);
                });

                $(`#${modalId}CloseBtn`).click();
            });
        }

        const loadCitiesIntoTable = (tableId, response) => {
            const tbody = response.data.map(city => {
                return `
                    <tr>
                        <td>${ city.id }</td>
                        <td>${ city.name }</td>
                        <td>${ city.count_incoming_fligths ?? 0 }</td>
                        <td>${ city.count_outgoing_flights ?? 0 }</td>
                        <td>
                            <button
                                id=""
                                type="button"
                                onclick=""
                                class='button border rounded-full px-5 py-2 hover:text-white bg-white'
                            >
                                Edit
                            </button>
                            <button
                                id=""
                                type="button"
                                onclick=""
                                class='button border rounded-full px-5 py-2 hover:text-white bg-white'
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                `;
            });

            $("a[rel='next']").attr('href', response.next_page_url);
            $(`#${tableId}Tbody`).empty().append(tbody);
        }

        const clearForm = (formId) => {
            document.getElementById(formId).reset();
            clearErrorsFromForm(formId);
        }

        const ajaxRequest = (url, formId, method, success) => {
            $.ajax(url, {
                data: formId ? $(`#${formId}`).serialize() : null,    
                dataType: 'json',
                headers: {
                    Accept: 'application/json'
                },
                method,
                beforeSend: function () {
                    if (formId) {
                        clearErrorsFromForm(formId);
                    }
                },
                success,
                error: function (response) {
                    displayErrorsFromResponse(response);
                }
            });
        }
    </script>
@endsection