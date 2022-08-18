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

    @php 
        $createFormId = 'newAirlineForm';
    @endphp

    <x-modal 
        id="newAirlineModal"
        title="New airline"
        submitBtnLabel="Save" 
        submitBtnOnclick="saveAirline(newAirlineModal.id)"
        closeBtnOnclick="clearForm({{ $createFormId }}.id)"
    >
        <form id="{{ $createFormId }}" onsubmit="saveAirline(newAirlineModal.id)">
            <x-form-input-container :forForm="$createFormId" forInput="name">
                <x-label :forForm="$createFormId" for="name">Name</x-label>

                <x-input
                    :forForm="$createFormId"
                    name="name"
                    placeholder="American Airlines"
                    class="block w-full"
                />
            </x-form-input-container>

            <x-form-input-container
                :forForm="$createFormId"
                forInput="description"
                class="mt-3"
            >
                <x-label :forForm="$createFormId" for="description">Description</x-label>

                <x-input
                    :forForm="$createFormId"
                    name="description"
                    placeholder="American Airlines was founded in 1978"
                    class="block w-full"
                />
            </x-form-input-container>

            <x-form-input-container
                :forForm="$createFormId"
                forInput="cities"
                class="mt-3"
            >
                <x-label :forForm="$createFormId" for="cities">Cities</x-label>

                <x-dropdown id="{{ $createFormId }}-cities" class="hover:text-white dark:hover:bg-blue-700 w-full text-left" onchange="addCityToSelectedCities(this, '{{ $createFormId }}')">
                    <option value="" selected>Choose</option>
    
                    @forelse($cities as $city)
                        <option value="{{ $city->id }}">{{ $city->name }}</option>
                    @empty
                        <option value="" disabled>No cities available</option>
                    @endforelse
                </x-dropdown>

                <div id="{{ $createFormId }}-selectedCitiesContainer" class="mt-4 flex flex-wrap hidden"></div>
            </x-form-input-container>
        </form>
    </x-modal>

    @php
        $editFormId = 'editAirlineForm';
    @endphp

    <x-modal 
        id="editAirlineModal"
        title="Edit airline"
        submitBtnLabel="Update" 
        closeBtnOnclick="clearForm({{ $editFormId }}.id)"
    >
        <form id="{{ $editFormId }}">
            <x-form-input-container :forForm="$editFormId" forInput="name">
                <x-label :forForm="$editFormId" for="name">Name</x-label>

                <x-input
                    :forForm="$editFormId"
                    name="name"
                    placeholder="American Airlines"
                    class="block w-full"
                />
            </x-form-input-container>

            <x-form-input-container
                :forForm="$editFormId"
                forInput="description"
                class="mt-3"
            >
                <x-label :forForm="$editFormId" for="description">Description</x-label>

                <x-input
                    :forForm="$editFormId"
                    name="description"
                    placeholder="American Airlines was founded in 1978"
                    class="block w-full"
                />
            </x-form-input-container>

            <x-form-input-container
                :forForm="$editFormId"
                forInput="cities"
                class="mt-3"
            >
                <x-label :forForm="$editFormId" for="cities">Cities</x-label>

                <x-dropdown id="{{ $editFormId }}-cities" class="hover:text-white dark:hover:bg-blue-700 w-full text-left" onchange="addCityToSelectedCities(this, '{{ $editFormId }}')">
                    <option value="" selected>Choose</option>
    
                    @forelse($cities as $city)
                        <option value="{{ $city->id }}">{{ $city->name }}</option>
                    @empty
                        <option value="" disabled>No cities available</option>
                    @endforelse
                </x-dropdown>

                <div id="{{ $editFormId }}-selectedCitiesContainer" class="mt-2 flex flex-wrap hidden"></div>
            </x-form-input-container>
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

        let cities = @json($cities);
        let selectedCities = [];

        $('form').on('submit', function (e) {
            e.preventDefault();
        });

        $(document).ready(function () {
            loadAirlinesIntoTable(@json($airlines));

            setFilterAndSortingFieldsValues();
        });

        const getFormValues = (formId) => {
            const formData = new FormData(document.getElementById(formId));
            
            return Object.fromEntries(formData.entries());
        }

        const saveAirline = (modalId) => {
            const formId = 'newAirlineForm';

            clearErrorsFromForm(formId);

            fetch('{{ route("airlines.store") }}',{
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(getFormValues(formId))
            })
            .then((response) => {
                if (!response.ok) {
                    return response.text().then(text => {
                        const error = JSON.parse(text);

                        if (response.status == HTTP_UNPROCESSABLE_CONTENT) {
                            displayFormErrorsFromResponse(error.errors, formId);
                        } else {
                            throw new Error(error.message);
                        }
                    });
                }

                return response.json();
            })
            .then((response) => {
                if (response != undefined) {
                    clearForm(formId);

                    getAndLoadAirlines(false);

                    toggleModal(modalId);

                    Toast.success(response.message);
                }
            })
            .catch((error) => Toast.danger(error));
        }

        const editAirline = (airlineId) => {
            const url = '{{ route("airlines.show", ["airline" => "airlineId"]) }}'.replace('airlineId', airlineId);

            fetch(url,{
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then((response) => {
                if (!response.ok) {
                    return response.text().then(text => {
                        const error = JSON.parse(text);
                        
                        throw new Error(error.message);
                    });
                }

                return response.json();
            })
            .then((response) => {
                if (response != undefined) {
                    const airline = response.data;
                    const modalId = 'editAirlineModal';
                    const formId = 'editAirlineForm';
                    const onclick = `updateAirline('${modalId}', ${airlineId})`;

                    setInputValue(`${formId}-name`, airline.name);
                    setInputValue(`${formId}-description`, airline.description);

                    selectedCities = airline.cities;

                    if (selectedCities.length) {
                        removeSelectedCitiesFromCities();
    
                        renderCitiesDropdownOptions(formId);
    
                        renderSelectedCitiesItems(formId)
    
                        showSelectedCitiesContainer(formId);
                    }

                    $(`#${modalId}SubmitBtn`).attr('onclick', onclick);
                    $(`#${formId}`).attr('onsubmit', onclick);
                    
                    toggleModal(modalId);
                }
            })
            .catch((error) => Toast.danger(error));
        }

        const updateAirline = (modalId, airlineId) => {
            const url = '{{ route("airlines.update", ["airline" => "airlineId"]) }}'.replace("airlineId", airlineId);

            const formId = 'editAirlineForm';

            clearErrorsFromForm(formId);

            fetch(url,{
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(getFormValues(formId))
            })
            .then((response) => {
                if (!response.ok) {
                    return response.text().then(text => {
                        const error = JSON.parse(text);

                        if (response.status == HTTP_UNPROCESSABLE_CONTENT) {
                            displayFormErrorsFromResponse(error.errors, formId);
                        } else {
                            throw new Error(error.message);
                        }
                    });
                }

                return response.json();
            })
            .then((response) => {
                if (response != undefined) {
                    clearForm(formId);

                    getAndLoadAirlines();

                    toggleModal(modalId);

                    Toast.success(response.message);
                }
            })
            .catch((error) => Toast.danger(error));
        }

        const deleteAirline = (airlineId, confirm = false) => {
            const url = '{{ route("airlines.destroy", ["airline" => "airlineId"]) }}'.replace("airlineId", airlineId);

            const modalId = 'deleteAirlineModal';

            fetch(url,{
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    confirmation: confirm
                })
            })
            .then((response) => {
                if (!response.ok) {
                    return response.text().then(text => {
                        const message = JSON.parse(text).message;

                        if (response.status == HTTP_UNPROCESSABLE_CONTENT) {
                            $(`#${modalId}Title`).text("Delete airline confirmation");
                            $(`#${modalId}Message`).html(message);
                            $(`#${modalId}SubmitBtn`).attr('onclick', `deleteAirline(${airlineId}, true)`).text("Confirm");
                        } else {
                            throw new Error(message);
                        }
                    });
                }

                return response.json();
            })
            .then((response) => {
                if (response != undefined) {
                    getAndLoadAirlines();

                    toggleModal(modalId);

                    resetDeleteAirlineModal();

                    Toast.success(response.message);
                }
            })
            .catch((error) => Toast.danger(error));
        }

        const removeSelectedCitiesFromCities = () => {
            selectedCities.forEach(({id: selectedCityId}) => {
                cities = cities.filter(city => city.id != selectedCityId);
            });
        }

        const addCityToSelectedCities = (element, formId) => {
            const id = element.value;

            selectedCities.push({
                id,
                name: $(`#${formId}-cities option[value=${id}]`).text()
            });

            removeSelectedCitiesFromCities();

            renderCitiesDropdownOptions(formId);

            renderSelectedCitiesItems(formId);

            showSelectedCitiesContainer(formId);
        }

        const removeCityFromSelectedCities = (cityId, formId) => {
            const cityForDeletion = selectedCities.find(city => city.id == cityId);

            cities.push(cityForDeletion);

            selectedCities = selectedCities.filter(city => city != cityForDeletion);

            if (!selectedCities.length) {
                hideSelectedCitiesContainer(formId);
            }

            renderCitiesDropdownOptions(formId);

            renderSelectedCitiesItems(formId);
        }

        const getSelectedCitiesContainer = (formId) => {
            return $(`#${formId}-selectedCitiesContainer`);
        }

        const showSelectedCitiesContainer = (formId) => {
            const element = getSelectedCitiesContainer(formId);
            
            if (element.hasClass('hidden')) {
                element.removeClass('hidden');
            }
        }

        const hideSelectedCitiesContainer = (formId) => {
            const element = getSelectedCitiesContainer(formId);
            
            if (!element.hasClass('hidden')) {
                element.addClass('hidden');
            }
        }

        const renderSelectedCitiesItems = (formId) => {
            let html = ``;

            const selectedCitiesIds = [];

            selectedCities.forEach(city => {
                selectedCitiesIds.push(city.id);

                let name = city.name;

                if (name.length > 13) {
                    name = `${ city.name.substring(0, 10) }...`;
                }

                html += `
                    <span class="px-4 py-2 bg-blue-700 rounded-full text-white mr-3 mt-2" title="${ city.name }">
                        ${ name } <button type="button" title="Remove '${ city.name }' city" onclick="removeCityFromSelectedCities(${ city.id }, '${ formId }')" class="ml-2">x</button>
                    </span>
                `;
            });

            html += `<input type="hidden" name="cities" value="${selectedCitiesIds}">`;

            getSelectedCitiesContainer(formId).empty().append(html);
        }

        const renderCitiesDropdownOptions = (formId) => {
            let options = `<option value="" selected disabled>Choose</option>`;

            cities = orderCitiesAlphabetically(cities);

            cities.forEach(city => {
                options += `
                    <option value="${ city.id }">${ city.name } </option>
                `;
            });

            $(`#${formId}-cities`).empty().append(options);
        }

        const orderCitiesAlphabetically = (cities) => {
            return cities.sort((x, y) => {
                return x.name.localeCompare(y.name);
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

            $(`#${modalId}Message`).html(`Are you sure you want to delete <strong>${airlineName} (ID ${airlineId})</strong> airline?`);
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
            
            if (selectedCities.length) {
                getSelectedCitiesContainer(formId).empty();

                cities = orderCitiesAlphabetically(cities.concat(selectedCities));

                selectedCities = [];

                renderCitiesDropdownOptions(formId);

                hideSelectedCitiesContainer(formId);
            }

            clearErrorsFromForm(formId);
        }
    </script>
@endsection