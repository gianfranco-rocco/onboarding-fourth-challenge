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

                <x-dropdown id="airline" name="airline" class="hover:text-white dark:hover:bg-blue-700 w-fit" onchange="handleSortingAndFiltering(this)">
                    <option value="airline=" selected>None</option>
    
                    @forelse($airlines as $airline)
                        <option value="airline={{ $airline->id }}">{{ $airline->name }}</option>
                    @empty
                        <option value="" disabled>No airlines available</option>
                    @endforelse
                </x-dropdown>
            </div>

            <div class="mb-4">
                <label for="sortBy" class="mr-3">Sort by</label>

                <x-dropdown id="sortBy" name="sort-by" class="hover:text-white dark:hover:bg-blue-700 w-fit" onchange="handleSortingAndFiltering(this)">
                    <option value="sort=id,asc">ID (ascending)</option>
                    <option value="sort=id,desc" selected>ID (descending)</option>
                    <option value="sort=name,asc">Name (ascending)</option>
                    <option value="sort=name,desc">Name (descending)</option>
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

    @php
        $createFormId = 'newCityForm';
    @endphp

    <x-modal 
        id="newCityModal"
        title="New city"
        submitBtnLabel="Save" 
        submitBtnOnclick="saveCity(newCityModal.id)"
        closeBtnOnclick="clearForm({{ $createFormId }}.id)"
    >
        <form id="{{ $createFormId }}" onsubmit="saveCity(newCityModal.id)">
            <x-form-input-container :forForm="$createFormId" forInput="name">
                <x-label :forForm="$createFormId" for="name">Name</x-label>

                <x-input
                    :forForm="$createFormId"
                    name="name"
                    placeholder="Montevideo"
                    class="block w-full"
                />
            </x-form-input-container>
        </form>
    </x-modal>

    @php
        $editFormId = 'editCityForm';
    @endphp

    <x-modal 
        id="editCityModal"
        title="Edit city"
        submitBtnLabel="Update" 
        closeBtnOnclick="clearForm({{ $editFormId }}.id)"
    >
        <form id="{{ $editFormId }}">
            <x-form-input-container :forForm="$editFormId" forInput="name">
                <x-label :forForm="$editFormId" for="name">Name</x-label>

                <x-input
                    :forForm="$editFormId"
                    name="name"
                    placeholder="Montevideo"
                    class="block w-full"
                />
            </x-form-input-container>
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

        $('form').on('submit', function (e) {
            e.preventDefault();
        });

        $(document).ready(function () {
            loadCitiesIntoTable(@json($cities));

            setFilterAndSortByDropdownValue();
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
                    const formId = 'editCityForm';
                    const onclick = `updateCity('${modalId}', ${cityId})`;

                    setInputValue(`${formId}-name`, response.data.name);

                    $(`#${modalId}SubmitBtn`).attr('onclick', onclick);
                    $(`#${formId}`).attr('onsubmit', onclick);
                    
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

        const handleSortingAndFiltering = (event) => {
            let queryParams = [];

            const queryStrings = currentQueryStringArr();

            if (queryStrings.length) {
                queryParams = queryStrings.map(queryString => {
                    const [key, value] = queryString.split("=");

                    return [key, value];
                });

                queryParams = Object.fromEntries(queryParams);                
            }

            const [newQueryStringKey, newQueryStringValue] = event.value.split("=");

            if (newQueryStringValue) {
                queryParams[newQueryStringKey] = newQueryStringValue;
            } else {
                if (newQueryStringKey in queryParams) {
                    delete queryParams[newQueryStringKey];
                }
            }

            const queryString = Object.keys(queryParams).map(key => key + '=' + queryParams[key]).join('&');

            window.location.replace(`{{ route("cities.index") }}?${queryString}`);
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

        const setFilterAndSortByDropdownValue = () => {
            currentQueryStringArr().forEach(queryString => {
                if (queryString) {
                    const sortByElement = $(`#sortBy option[value='${queryString}']`);
                    const airlineElement = $(`#airline option[value='${queryString}']`);

                    if (sortByElement.length) {
                        $("#sortBy").val(queryString);
                    } else if (airlineElement.length) {
                        $("#airline").val(queryString);
                    }
                }
            });
        }

        const currentQueryStringArr = () => {
            /**
             * We do substring(1) to remove the '?' from the beginning of query params
             */
            const currSearch = window.location.search.substring(1);

            return currSearch ? currSearch.split("&") : [];
        }

        const loadCitiesIntoTable = (response) => {
            const tbody = response.data.length
            ? response.data.map(city => {
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
            })
            : `
                <tr>
                    <td colspan=5 class="text-center">No cities available</td>
                </tr>
            `;

            $("a[rel='next']").attr('href', response.next_page_url);
            $(`#citiesTableTbody`).empty().append(tbody);
        }

        const clearForm = (formId) => {
            document.getElementById(formId).reset();
            clearErrorsFromForm(formId);
        }
    </script>
@endsection