@extends('layout')

@section ('title')
    Cities
@endsection

@section('body')
    <div class="flex justify-between">
        <div class="flex items-center">
            <x-button data-modal-toggle="newCityModal" class="mb-4 hover:bg-blue-700">New city</x-button>
        </div>

        <div class="flex flex-col items-end">
            <div class="mb-4">
                <label for="airline" class="mr-3">Filter by airline</label>

                <x-dropdown id="airline" name="airline" class="hover:text-white dark:hover:bg-blue-700 w-fit" onchange="getCitiesByAirline(this)">
                    <option value="" selected>None</option>
    
                    @forelse($airlines as $airline)
                        <option value="{{ $airline->id }}">{{ $airline->name }}</option>
                    @empty
                        <option value="" disabled>No airlines available</option>
                    @endforelse
                </x-dropdown>
            </div>

            <div class="mb-4">
                <label for="sortBy" class="mr-3">Sort by</label>

                <x-dropdown id="sortBy" name="sort-by" class="hover:text-white dark:hover:bg-blue-700 w-fit" onchange="sortBy(this)">
                    <option value="{{ route('cities.index', ['sort' => 'id', 'sort_dir' => 'asc']) }}">ID (ascending)</option>
                    <option value="{{ route('cities.index', ['sort' => 'id', 'sort_dir' => 'desc']) }}" selected>ID (descending)</option>
                    <option value="{{ route('cities.index', ['sort' => 'name', 'sort_dir' => 'asc']) }}">Name (ascending)</option>
                    <option value="{{ route('cities.index', ['sort' => 'name', 'sort_dir' => 'desc']) }}">Name (descending)</option>
                </x-dropdown>
            </div>
        </div>
    </div>

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

    <x-modal 
        id="deleteCityModal"
        title="Delete city"
        submitBtnLabel="Delete"
    >
        <p id="deleteCityModalMessage"></p>
    </x-modal>
@endsection

@section('scripts')
    <script>
        const HTTP_UNPROCESSABLE_CONTENT = 422;

        $(document).ready(function () {
            loadCitiesIntoTable(@json($cities));

            const currUrl = window.location.href;

            $('#sortBy option').each(function() {
                if (this.value == currUrl) {
                    $("#sortBy").val(currUrl);
                }
            });

            console.log('currUrl', currUrl);
        });

        const saveCity = (modalId) => {
            const formId = 'newCityForm';

            $.ajax('{{ route("cities.store") }}', {
                data: $(`#${formId}`).serialize(),
                dataType: 'json',
                headers: {
                    Accept: 'application/json'
                },
                method: 'POST',
                beforeSend: function () {
                    clearErrorsFromForm(formId);
                },
                success: function (response) {
                    clearForm(formId);

                    getAndLoadCities(false);

                    toggleModal(modalId);

                    Toast.success(response.message);
                },
                error: function (response) {
                    if (response.status === HTTP_UNPROCESSABLE_CONTENT) {
                        displayFormErrorsFromResponse(response, formId);
                    } else {
                        Toast.danger(response.responseJSON.message);
                    }
                },
            });
        }


        const editCity = (cityId) => {
            const url = '{{ route("cities.show", ["city" => "cityId"]) }}'.replace('cityId', cityId);

            $.ajax(url, {
                headers: {
                    Accept: 'application/json'
                },
                method: 'GET',
                success: function (response) {
                    const modalId = 'editCityModal';

                    setInputValue(`editCityForm-name`, response.data.name);

                    $(`#${modalId}SubmitBtn`).attr('onclick', `updateCity('${modalId}', ${cityId})`);
                    
                    toggleModal(modalId);
                },
                error: function (response) {
                    Toast.danger(response.responseJSON.message);
                }
            });
        }

        const updateCity = (modalId, cityId) => {
            const url = '{{ route("cities.update", ["city" => "cityId"]) }}'.replace("cityId", cityId);

            const formId = 'editCityForm';

            $.ajax(url, {
                data: $(`#${formId}`).serialize(),
                dataType: 'json',
                headers: {
                    Accept: 'application/json'
                },
                method: 'PUT',
                beforeSend: function () {
                    clearErrorsFromForm(formId);
                },
                success: function (response) {
                    clearForm(formId);

                    getAndLoadCities();
                    
                    toggleModal(modalId);

                    Toast.success(response.message);
                },
                error: function (response) {
                    if (response.status === HTTP_UNPROCESSABLE_CONTENT) {
                        displayFormErrorsFromResponse(response, formId);
                    } else {
                        Toast.danger(response.responseJSON.message);
                    }
                }
            });
        }

        const deleteCity = (cityId, confirm = false) => {
            const url = '{{ route("cities.destroy", ["city" => "cityId"]) }}'.replace("cityId", cityId);

            const modalId = 'deleteCityModal';

            $.ajax(url, {
                data: {
                    confirmation: confirm
                },
                dataType: 'json',
                headers: {
                    Accept: 'application/json'
                },
                method: 'DELETE',
                success: function (response) {
                    getAndLoadCities();
                    toggleModal(modalId);
                    resetDeleteCityModal();

                    Toast.success(response.message);
                },
                error: function (response) {
                    const message = response.responseJSON.message;

                    if (response.status === HTTP_UNPROCESSABLE_CONTENT) {
                        $(`#${modalId}Title`).text("Delete city confirmation");
                        $(`#${modalId}Message`).html(message);
                        $(`#${modalId}SubmitBtn`).attr('onclick', `deleteCity(${cityId}, true)`).text("Confirm");
                    } else {
                        Toast.danger(message);
                    }
                }
            });
        }

        const getCitiesByAirline = (event) => {
            const airlineId = event.value;

            const url = airlineId
                        ? '{{ route("cities.getByAirline", ["airline" => "airlineId"]) }}'.replace("airlineId", airlineId)
                        : '{{ route("cities.index") }}';

            $.ajax(url, {
                headers: {
                    Accept: 'application/json'
                },
                method: 'GET',
                success: function (response) {
                    loadCitiesIntoTable(response);
                },
                error: function (response) {
                    Toast.danger("An error occurred while getting cities.");
                },
            });
        }

        const sortBy = (event) => {
            window.location.replace(event.value);
        }

        const resetDeleteCityModal = () => {
            const modalId = 'deleteCityModal';

            $(`#${modalId}Title`).text("Delete city");
            $(`#${modalId}SubmitBtn`).text("Delete");
        }

        const openDeleteCityModal = (cityId, cityName) => {
            const modalId = 'deleteCityModal';

            $(`#${modalId}Message`).text(`Are you sure you want to delete '${cityName}' city?`);
            $(`#${modalId}SubmitBtn`).attr('onclick', `deleteCity(${cityId})`);

            toggleModal(modalId);
        }

        const getAndLoadCities = (withCursor = true) => {
            const url = withCursor 
                        ? '{{ route("cities.index", ["cursor" => request()->get("cursor")]) }}'
                        : '{{ route("cities.index") }}';

            $.ajax(url, {
                headers: {
                    Accept: 'application/json'
                },
                method: 'GET',
                success: function (response) {
                    loadCitiesIntoTable(response);
                },
                error: function (response) {
                    Toast.danger("An error occurred while refreshing records.");
                },
            });
        }

        const loadCitiesIntoTable = (response) => {
            const tbody = response.data.map(city => {
                return `
                    <tr>
                        <td>${ city.id }</td>
                        <td>${ city.name }</td>
                        <td>${ city.incoming_flights_count }</td>
                        <td>${ city.outgoing_flights_count }</td>
                        <td>
                            <x-button onclick="editCity(${ city.id })">Edit</x-button>
                            <x-button onclick="openDeleteCityModal(${ city.id }, '${city.name}')">Delete</x-button>
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
    </script>
@endsection