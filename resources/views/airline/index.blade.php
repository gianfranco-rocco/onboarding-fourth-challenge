@extends('layout')

@section ('title', 'Airlines')

@section('body')
    <div class="flex justify-between">
        <div class="flex items-center">
            <x-button data-modal-toggle="newAirlineModal" class="mb-4 hover:bg-blue-700">New airline</x-button>
        </div>

        <div class="flex flex-col items-end">
            <div class="mb-4">
                <label for="destinationCity" class="mr-3">Filter by destination city</label>

                <x-dropdown id="destinationCity" name="destination_city" class="hover:text-white dark:hover:bg-blue-700 w-fit" onchange="handleDropdownFiltering(this)">
                    <option value="destination_city=" selected>None</option>
    
                    @forelse($cities as $city)
                        <option value="destination_city={{ $city->id }}">{{ $city->name }}</option>
                    @empty
                        <option value="" disabled>No cities available</option>
                    @endforelse
                </x-dropdown>
            </div>

            <div class="mb-4 flex items-center">
                <label for="activeFlights" class="mr-3">Filter by active flights</label>

                <x-input
                    id="activeFlights"
                    name="active_flights"
                    type="number"
                    placeholder="Amount"
                    class="mr-4"
                />

                <x-button onclick="handleInputFiltering('activeFlights')">Filter</x-button>
            </div>
        </div>
    </div>

    <x-table id="airlinesTable" :records="$airlines">
        <x-slot:heading>
            <th>ID</th>
            <th>Name</th>
            <th>Description</th>
            <th>Active Flights</th>
            <th></th>
        </x-slot:heading>
    </x-table>

    <x-modal 
        id="newAirlineModal"
        title="New airline"
        submitBtnLabel="Save" 
        submitBtnOnclick="saveAirline(newAirlineModal.id)"
        closeBtnOnclick="clearForm(newAirlineForm.id)"
    >
        <form id="newAirlineForm" onsubmit="saveAirline(newAirlineModal.id)">
            <x-form-input-container
                formId="newAirlineForm"
                name="name"
                label="Name"
                placeholder="American Airlines"
            />

            <x-form-input-container
                formId="newAirlineForm"
                name="description"
                label="Description"
                placeholder="Description"
                class="mt-3"
            />
        </form>
    </x-modal>

    <x-modal 
        id="editAirlineModal"
        title="Edit airline"
        submitBtnLabel="Update" 
        closeBtnOnclick="clearForm(editAirlineForm.id)"
    >
        <form id="editAirlineForm">
            <x-form-input-container
                formId="editAirlineForm"
                name="name"
                label="Name"
                placeholder="American Airlines"
            />
        </form>
    </x-modal>

    <x-modal 
        id="deleteAirlineModal"
        title="Delete airline"
        submitBtnLabel="Delete"
    >
        <p id="deleteAirlineModalMessage"></p>
    </x-modal>
@endsection

@section('scripts')
    <script>
        const HTTP_UNPROCESSABLE_CONTENT = 422;

        $('form').on('submit', function (e) {
            e.preventDefault();
        });

        $(document).ready(function () {
            loadAirlinesIntoTable(@json($airlines));

            setFilterAndSortingFieldsValues();
        });

        const saveAirline = (modalId) => {
            const formId = 'newAirlineForm';

            $.ajax('{{ route("airlines.store") }}', {
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

                    getAndLoadAirlines(false);

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

        const editAirline = (airlineId) => {
            const url = '{{ route("airlines.show", ["airline" => "airlineId"]) }}'.replace('airlineId', airlineId);

            $.ajax(url, {
                headers: {
                    Accept: 'application/json'
                },
                method: 'GET',
                success: function (response) {
                    const modalId = 'editAirlineModal';
                    const formId = 'editAirlineForm';
                    const onclick = `updateAirline('${modalId}', ${airlineId})`;

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

        const updateAirline = (modalId, airlineId) => {
            const url = '{{ route("airlines.update", ["airline" => "airlineId"]) }}'.replace("airlineId", airlineId);

            const formId = 'editAirlineForm';

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

                    getAndLoadAirlines();
                    
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

        const deleteAirline = (airlineId, confirm = false) => {
            const url = '{{ route("airlines.destroy", ["airline" => "airlineId"]) }}'.replace("airlineId", airlineId);

            const modalId = 'deleteAirlineModal';

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
                    getAndLoadAirlines();
                    toggleModal(modalId);
                    resetDeleteAirlineModal();

                    Toast.success(response.message);
                },
                error: function (response) {
                    const message = response.responseJSON.message;

                    if (response.status === HTTP_UNPROCESSABLE_CONTENT) {
                        $(`#${modalId}Title`).text("Delete airline confirmation");
                        $(`#${modalId}Message`).html(message);
                        $(`#${modalId}SubmitBtn`).attr('onclick', `deleteAirline(${airlineId}, true)`).text("Confirm");
                    } else {
                        Toast.danger(message);
                    }
                }
            });
        }

        const handleInputFiltering = (inputId) => {
            const input = document.getElementById(inputId);

            handleFiltering(input.name, input.value);
        }

        const extractQueryStringData = (queryString) => {
            const [key, value] = queryString.split("=");

            return {key, value};
        }

        const handleDropdownFiltering = (event) => {
            const {key, value} = extractQueryStringData(event.value);

            handleFiltering(key, value);
        }

        const handleFiltering = (newQueryStringKey, newQueryStringValue) => {
            let queryParams = [];

            const currentQueryString = currentQueryStringArr();

            if (currentQueryString.length) {
                queryParams = currentQueryString.map(queryString => {
                    const {key, value} = extractQueryStringData(queryString);

                    return [key, value];
                });

                queryParams = Object.fromEntries(queryParams);                
            }

            if (newQueryStringValue) {
                queryParams[newQueryStringKey] = newQueryStringValue;
            } else {
                if (newQueryStringKey in queryParams) {
                    delete queryParams[newQueryStringKey];
                }
            }

            const queryString = Object.keys(queryParams).map(key => key + '=' + queryParams[key]).join('&');

            window.location.replace(`{{ route("airlines.index") }}?${queryString}`);
        }

        const resetDeleteAirlineModal = () => {
            const modalId = 'deleteAirlineModal';

            $(`#${modalId}Title`).text("Delete airline");
            $(`#${modalId}SubmitBtn`).text("Delete");
        }

        const openDeleteAirlineModal = (airlineId, airlineName) => {
            const modalId = 'deleteAirlineModal';

            $(`#${modalId}Message`).text(`Are you sure you want to delete '${airlineName}' airline?`);
            $(`#${modalId}SubmitBtn`).attr('onclick', `deleteAirline(${airlineId})`);

            toggleModal(modalId);
        }

        const getAndLoadAirlines = (withCursor = true) => {
            const url = withCursor 
                        ? '{{ route("airlines.index", ["cursor" => request()->get("cursor")]) }}'
                        : '{{ route("airlines.index") }}';

            $.ajax(url, {
                headers: {
                    Accept: 'application/json'
                },
                method: 'GET',
                success: function (response) {
                    loadAirlinesIntoTable(response.airlines);
                },
                error: function (response) {
                    Toast.danger("An error occurred while refreshing records.");
                },
            });
        }

        const setFilterAndSortingFieldsValues = () => {
            currentQueryStringArr().forEach(queryString => {
                if (queryString) {
                    const {key, value} = extractQueryStringData(queryString);

                    switch(key) {
                        case 'active_flights':
                            if (!isNaN(value)) {
                                $('#activeFlights').val(value);
                            }

                            break;
                        case 'destination_city':
                            const destinationCityElement = $(`#destinationCity option[value='${queryString}']`);
        
                            if (destinationCityElement.length) {
                                $("#destinationCity").val(queryString);
                            }

                            break;
                        default:
                            break;
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

        const loadAirlinesIntoTable = (response) => {
            const tbody = response.data.length
            ? response.data.map(airline => {
                return `
                    <tr>
                        <td>${ airline.id }</td>
                        <td>${ airline.name }</td>
                        <td>${ airline.description }</td>
                        <td>${ airline.active_flights_count }</td>
                        <td>
                            <x-button onclick="editAirline(${ airline.id })">Edit</x-button>
                            <x-button onclick="openDeleteAirlineModal(${ airline.id }, '${ airline.name }')">Delete</x-button>
                        </td>
                    </tr>
                `;
            })
            : `
                <tr>
                    <td colspan=5 class="text-center">No airlines available</td>
                </tr>
            `;

            $("a[rel='next']").attr('href', response.next_page_url);
            $(`#airlinesTableTbody`).empty().append(tbody);
        }

        const clearForm = (formId) => {
            document.getElementById(formId).reset();
            clearErrorsFromForm(formId);
        }
    </script>
@endsection