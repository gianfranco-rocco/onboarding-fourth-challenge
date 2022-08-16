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
                formId="newCityForm"
                name="name"
                label="Name"
                placeholder="Montevideo"
            />
        </form>
    </x-modal>

    <x-modal 
        id="editCityModal"
        title="Edit city"
        submitBtnLabel="Update" 
        submitBtnOnclick="updateCity(editCityModal.id)"
        closeBtnOnclick="clearForm(editCityForm.id)"
    >
        <form id="editCityForm">
            <x-form-input-container
                formId="editCityForm"
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
            loadCitiesIntoTable(@json($cities));
        });

        const saveCity = (modalId) => {
            const formId = 'newCityForm';

            ajaxRequest(
                '{{ route("cities.store") }}',
                $(`#${formId}`).serialize(),
                'POST',
                function () {
                    clearErrorsFromForm(formId);
                },
                function (response) {
                    clearForm(formId);

                    getAndLoadCities();

                    toggleModal(modalId);
                },
                function (response) {
                    displayFormErrorsFromResponse(response, formId);
                }
            );
        }


        const editCity = (cityId) => {
            const formId = 'editCityForm';

            const url = '{{ route("cities.show", ["city" => "cityId"]) }}'.replace('cityId', cityId);

            ajaxRequest(
                url, 
                null, 
                'GET',
                null,
                function (response) {
                    const modalId = 'editCityModal';

                    setInputValue(`${formId}-name`, response.data.name);

                    $(`#${modalId}SubmitBtn`).attr('onclick', `updateCity('${modalId}', ${cityId})`);
                    
                    toggleModal(modalId);
                }
            );
        }

        const updateCity = (modalId, cityId) => {
            const formId = 'editCityForm';

            const url = '{{ route("cities.update", ["city" => "cityId"]) }}'.replace("cityId", cityId);

            ajaxRequest(
                url,
                $(`#${formId}`).serialize(),
                'PUT',
                function () {
                    clearErrorsFromForm(formId);
                },
                function (response) {
                    clearForm(formId);

                    getAndLoadCities();
                    
                    toggleModal(modalId);
                },
                function (response) {
                    displayFormErrorsFromResponse(response, formId);
                }
            );
        }

        const getAndLoadCities = () => {
            ajaxRequest('{{ route("cities.index") }}', null, 'GET', null, function (response) {
                loadCitiesIntoTable(response);
            });
        }

        const loadCitiesIntoTable = (response) => {
            const tbody = response.data.map(city => {
                return `
                    <tr>
                        <td>${ city.id }</td>
                        <td>${ city.name }</td>
                        <td>${ city.count_incoming_fligths ?? 0 }</td>
                        <td>${ city.count_outgoing_flights ?? 0 }</td>
                        <td>
                            <x-button onclick="editCity(${ city.id })">Edit</x-button>
                            <x-button>Delete</x-button>
                        </td>
                    </tr>
                `;
            });

            $("a[rel='next']").attr('href', response.next_page_url);
            $(`#citiesTableTbody`).empty().append(tbody);
        }

        const clearForm = (formId) => {
            document.getElementById(formId).reset();
            clearErrorsFromForm(formId);
        }

        const ajaxRequest = (url, data, method, beforeSend = null, success = null, error = null) => {
            $.ajax(url, {
                data,
                dataType: 'json',
                headers: {
                    Accept: 'application/json'
                },
                method,
                beforeSend,
                success,
                error,
                complete: (response) => { return response }
            });
        }
    </script>
@endsection