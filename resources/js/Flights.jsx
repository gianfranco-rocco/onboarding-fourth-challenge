import React, { useEffect, useRef, useState } from 'react';
import { createRoot } from 'react-dom/client'
import Table from './components/Table';
import Paginator from './components/Table/Paginator';
import useFlights from './hooks/useFlights';
import FlightItem from './components/Table/FlightItem';
import Modal from './components/Modal';
import InputContainer from './components/Form/InputContainer';
import Label from './components/Form/Label';
import Input from './components/Form/Input';
import Button from './components/Button';
import useAirlines from './hooks/useAirlines';
import Dropdown from './components/Dropdown';
import useCities from './hooks/useCities';
import Error from './components/Form/Error';
import { ToastContainer } from 'react-toastify';

export default function Flights() {
    const fieldErrorClasses = 'bg-red-50 border-red-500 text-red-900 placeholder-red-700 focus:ring-red-500 focus:border-red-500 dark:border-red-400';

    const [showCreateModal, setShowCreateModal] = useState(false);
    const [showEditModal, setShowEditModal] = useState(false);
    const [showDeleteModal, setShowDeleteModal] = useState(false);

    const deleteFlightMessageRef = useRef('');

    const {
        flights,
        paginator,
        getFlights,
        saveFlight,
        updateFlight,
        getFlight,
        deleteFlight,
        params,
        handlePagination,
        flightData,
        setFlightData,
        getFormErrors,
        hasFormErrors,
        setFormErrors,
        removeFormErrors,
        handleFiltering
    } = useFlights();

    const {
        airlines,
        getAirlines,
    } = useAirlines();

    const {
        cities,
        departureCities,
        destinationCities,
        getCities,
        getDepartureCities,
        getDestinationCities,
        setDepartureCities,
        setDestinationCities
    } = useCities();

    useEffect(() => {
        getAirlines();
        getCities();
    }, []);

    const clearForm = (setShowModal) => {
        setFlightData({});
        setShowModal(showModal => (!showModal));
        setFormErrors({});
    }

    const handleFormDataChange = (e) => {
        const key = e.target.name;

        setFlightData(data => ({
            ...data,
            [key]: e.target.value
        }));

        removeFormErrors(key);
    }

    const handleAirlineChange = async (e) => {
        handleFormDataChange(e);
        getDepartureCities(e.target.value);
        setDestinationCities([]);

        setFlightData(data => ({
            ...data,
            departure_city: '',
            destination_city: ''
        }));
    }

    const handleDepartureCityChange = (e) => {
        handleFormDataChange(e);
        getDestinationCities(e.target.value);

        setFlightData(data => ({
            ...data,
            destination_city: ''
        }));
    }

    const handleFlightEdit = async (flightId) => {
        const flight = await getFlight(flightId);

        if (flight) {
            setFlightData(flight);
    
            setDepartureCities(flight.departure_cities);
    
            setDestinationCities(flight.destination_cities);

            setShowEditModal(true);
        }
    }

    const handleFlightDelete = async (flightId) => {
        setFlightData({id: flightId});

        setShowDeleteModal(true);

        deleteFlightMessageRef.current = `Are you sure you want to delete flight 'ID ${flightId}'?`;
    }

    const getLabelClassNames = (key) => {
        const classNames = 'text-red-700 dark:text-red-500';

        /**
         * If a single key if passed to the function, then we check
         * if there is a form error related to that key.
         * If an object, more specifically an array, is passed to the function,
         * we check of any of the passed keys has a form error related to it. If
         * any of them do, the class names are returned.
         * 
         * This is useful for when one label corresponds to 2 or more different fields
         * and you want to append the error class names to the label if either of those
         * fields' got a form error.
         */
        switch(typeof(key)) {
            case 'string':
                return hasFormErrors(key) && classNames;
            case 'object':
                const keysWithFormErrors = key.filter(k => hasFormErrors(k));

                return keysWithFormErrors.length && classNames;
            default:
                return '';
        }
    }

    const getInputClassNames = (key) => {
        return hasFormErrors(key) ? fieldErrorClasses : '';
    }

    const getDropdownClassNames = (key) => {
        let classNames = 'hover:text-white dark:hover:bg-blue-700 w-full text-left';

        if (hasFormErrors(key)) {
            classNames += ` ${fieldErrorClasses}`;
        }

        return classNames;
    }

    return(
        <>
            <ToastContainer />

            <div className="flex justify-between mb-4">
                <div className="flex items-center">
                    <Button onClick={() => setShowCreateModal(currValue => (!currValue))} classNames='mb-4 hover:bg-blue-700'>New flight</Button>
                </div>

                <div className="flex flex-col items-end">
                    <div className="mb-4">
                        <label htmlFor='airline-filter' className='mr-3'>Filter by airline</label>

                        <Dropdown
                            id="airline-filter"
                            name="airline"
                            onChange={handleFiltering}
                            classNames='hover:text-white dark:hover:bg-blue-700 w-fit'
                            options={airlines}
                            value={params.airline}
                            noOptionsLabel="No airlines available"
                            defaultOptionDisabled={false}
                        />
                    </div>

                    <div className="mb-4 flex">
                        <div className='mr-5'>
                            <label htmlFor='departure-city-filter' className='mr-3'>Filter by departure city</label>

                            <Dropdown
                                id="departure-city-filter"
                                name="departure_city"
                                onChange={handleFiltering}
                                classNames='hover:text-white dark:hover:bg-blue-700 w-fit'
                                options={cities}
                                value={params.departure_city}
                                noOptionsLabel="No departure cities available"
                                defaultOptionDisabled={false}
                            />
                        </div>
                        
                        <div>
                            <label htmlFor='destination-city-filter' className='mr-3'>Filter by destination city</label>

                            <Dropdown
                                id="destination-city-filter"
                                name="destination_city"
                                onChange={handleFiltering}
                                classNames='hover:text-white dark:hover:bg-blue-700 w-fit'
                                options={cities}
                                value={params.destination_city}
                                noOptionsLabel="No destination cities available"
                                defaultOptionDisabled={false}
                            />
                        </div>
                    </div>

                    <div className="mb-4 flex">
                        <div className='mr-5'>
                            <label htmlFor='departure-at-filter' className='mr-3'>Filter by departure date</label>

                            <Input
                                id='departure-at-filter'
                                onChange={handleFiltering}
                                type="date"
                                value={params.departure_at}
                                name="departure_at"
                            />
                        </div>
                        
                        <div>
                            <label htmlFor='arrival-at-filter' className='mr-3'>Filter by arrival date</label>

                            <Input
                                id='arrival-at-filter'
                                onChange={handleFiltering}
                                type="date"
                                value={params.arrival_at}
                                name="arrival_at"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <Table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Airline</th>
                        <th>Departure city</th>
                        <th>Departure at</th>
                        <th>Destination city</th>
                        <th>Arrival at</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    {
                        flights.length ?
                        flights.map(flight => (
                            <FlightItem key={flight.id} flight={flight} handleFlightEdit={handleFlightEdit} handleFlightDelete={handleFlightDelete} />
                        )) :
                        <tr>
                            <td colSpan={7} className='text-center'>No flights available</td>
                        </tr>
                    }
                </tbody>
            </Table>

            <Paginator paginator={paginator} handlePagination={handlePagination} />

            {/* Create modal */}
            <Modal
                title="New flight"
                submitBtnLabel="Save"
                submitBtnOnclick={() => saveFlight(setShowCreateModal)}
                closeBtnOnclick={() => clearForm(setShowCreateModal)}
                show={showCreateModal}
            >
                <form onSubmit={() => saveFlight(setShowCreateModal)}>
                    <InputContainer classNames='mb-4'>
                        <Label htmlFor="airline" classNames={getLabelClassNames('airline')}>Airline</Label>

                        <Dropdown
                            id="airline"
                            name="airline"
                            onChange={handleAirlineChange}
                            classNames={getDropdownClassNames('airline')}
                            options={airlines}
                            value={flightData.airline}
                            noOptionsLabel="No airlines available"
                        />

                        { getFormErrors('airline').map(error => <Error key={error}>{error}</Error>) }
                    </InputContainer>

                    <InputContainer classNames='mb-4'>
                        <Label htmlFor="departure_city" classNames={getLabelClassNames('departure_city')}>Departure city</Label>

                        <Dropdown
                            id="departure_city"
                            name="departure_city"
                            onChange={handleDepartureCityChange}
                            classNames={getDropdownClassNames('departure_city')}
                            options={departureCities}
                            disabled={!flightData.airline}
                            value={flightData.departure_city}
                            noOptionsLabel={
                                !flightData.airline ? 
                                'Choose airline' :
                                'No departure cities available'
                            }
                        />

                        { getFormErrors('departure_city').map(error => <Error key={error}>{error}</Error>) }
                    </InputContainer>

                    <InputContainer classNames='mb-4'>
                        <Label htmlFor="destination_city" classNames={getLabelClassNames('destination_city')}>Destination city</Label>

                        <Dropdown
                            id="destination_city"
                            name="destination_city"
                            onChange={handleFormDataChange}
                            classNames={getDropdownClassNames('destination_city')}
                            options={destinationCities}
                            disabled={!flightData.departure_city}
                            value={flightData.destination_city}
                            noOptionsLabel={
                                !flightData.departure_city ? 
                                'Choose departure city' :
                                'No destination cities available'
                            }
                        />

                        { getFormErrors('destination_city').map(error => <Error key={error}>{error}</Error>) }
                    </InputContainer>

                    <InputContainer classNames='mb-4'>
                        <Label htmlFor="departure_at" classNames={getLabelClassNames(['departure_at', 'departure_at_date', 'departure_at_time'])}>Departure at</Label>

                        <div className="flex">
                            <Input
                                id='departure_at_date'
                                onChange={handleFormDataChange}
                                type="date"
                                value={flightData.departure_at_date}
                                name="departure_at_date"
                                classNames={`flex-1 mr-4 ${getInputClassNames('departure_at_date')}`}
                            />

                            <Input
                                id='departure_at_time'
                                onChange={handleFormDataChange}
                                type="time"
                                value={flightData.departure_at_time}
                                name="departure_at_time"
                                classNames={getInputClassNames('departure_at_time')}
                            />
                        </div>

                        { getFormErrors('departure_at').map(error => <Error key={error}>{error}</Error>) }
                        { getFormErrors('departure_at_date').map(error => <Error key={error}>{error}</Error>) }
                        { getFormErrors('departure_at_time').map(error => <Error key={error}>{error}</Error>) }
                    </InputContainer>

                    <InputContainer>
                        <Label htmlFor="arrival_at" classNames={getLabelClassNames(['arrival_at', 'arrival_at_date', 'arrival_at_time'])}>Arrival at</Label>

                        <div className="flex">
                            <Input
                                id='arrival_at_date'
                                onChange={handleFormDataChange}
                                type="date"
                                value={flightData.arrival_at_date}
                                name="arrival_at_date"
                                classNames={`flex-1 mr-4 ${getInputClassNames('arrival_at_date')}`}
                            />

                            <Input
                                id='arrival_at_time'
                                onChange={handleFormDataChange}
                                type="time"
                                value={flightData.arrival_at_time}
                                name="arrival_at_time"
                                classNames={getInputClassNames('arrival_at_time')}
                            />
                        </div>

                        { getFormErrors('arrival_at').map(error => <Error key={error}>{error}</Error>) }
                        { getFormErrors('arrival_at_date').map(error => <Error key={error}>{error}</Error>) }
                        { getFormErrors('arrival_at_time').map(error => <Error key={error}>{error}</Error>) }
                    </InputContainer>
                </form>
            </Modal>

            {/* Edit modal */}
            <Modal
                title="Edit flight"
                submitBtnLabel="Update"
                submitBtnOnclick={() => updateFlight(setShowEditModal)}
                closeBtnOnclick={() => clearForm(setShowEditModal)}
                show={showEditModal}
            >
                <form onSubmit={() => updateFlight(setShowEditModal)}>
                    <InputContainer classNames='mb-4'>
                        <Label htmlFor="airline" classNames={getLabelClassNames('airline')}>Airline</Label>

                        <Dropdown
                            id="airline"
                            name="airline"
                            onChange={handleAirlineChange}
                            classNames={getDropdownClassNames('airline')}
                            options={airlines}
                            value={flightData.airline}
                            noOptionsLabel="No airlines available"
                        />

                        { getFormErrors('airline').map(error => <Error key={error}>{error}</Error>) }
                    </InputContainer>

                    <InputContainer classNames='mb-4'>
                        <Label htmlFor="departure_city" classNames={getLabelClassNames('departure_city')}>Departure city</Label>

                        <Dropdown
                            id="departure_city"
                            name="departure_city"
                            onChange={handleDepartureCityChange}
                            classNames={getDropdownClassNames('departure_city')}
                            options={departureCities}
                            disabled={!flightData.airline}
                            value={flightData.departure_city}
                            noOptionsLabel={
                                !flightData.airline ? 
                                'Choose airline' :
                                'No departure cities available'
                            }
                        />

                        { getFormErrors('departure_city').map(error => <Error key={error}>{error}</Error>) }
                    </InputContainer>

                    <InputContainer classNames='mb-4'>
                        <Label htmlFor="destination_city" classNames={getLabelClassNames('destination_city')}>Destination city</Label>

                        <Dropdown
                            id="destination_city"
                            name="destination_city"
                            onChange={handleFormDataChange}
                            classNames={getDropdownClassNames('destination_city')}
                            options={destinationCities}
                            disabled={!flightData.departure_city}
                            value={flightData.destination_city}
                            noOptionsLabel={
                                !flightData.departure_city ? 
                                'Choose departure city' :
                                'No destination cities available'
                            }
                        />

                        { getFormErrors('destination_city').map(error => <Error key={error}>{error}</Error>) }
                    </InputContainer>

                    <InputContainer classNames='mb-4'>
                        <Label htmlFor="departure_at" classNames={getLabelClassNames(['departure_at ', 'departure_at_date', 'departure_at_time'])}>Departure at</Label>

                        <div className="flex">
                            <Input
                                id='departure_at_date'
                                onChange={handleFormDataChange}
                                type="date"
                                value={flightData.departure_at_date}
                                name="departure_at_date"
                                classNames={`flex-1 mr-4 ${getInputClassNames('departure_at_date')}`}
                            />

                            <Input
                                id='departure_at_time'
                                onChange={handleFormDataChange}
                                type="time"
                                value={flightData.departure_at_time}
                                name="departure_at_time"
                                classNames={getInputClassNames('departure_at_time')}
                            />
                        </div>

                        { getFormErrors('departure_at').map(error => <Error key={error}>{error}</Error>) }
                        { getFormErrors('departure_at_date').map(error => <Error key={error}>{error}</Error>) }
                        { getFormErrors('departure_at_time').map(error => <Error key={error}>{error}</Error>) }
                    </InputContainer>

                    <InputContainer>
                        <Label htmlFor="arrival_at" classNames={getLabelClassNames(['arrival_at', 'arrival_at_date', 'arrival_at_time'])}>Arrival at</Label>

                        <div className="flex">
                            <Input
                                id='arrival_at_date'
                                onChange={handleFormDataChange}
                                type="date"
                                value={flightData.arrival_at_date}
                                name="arrival_at_date"
                                classNames={`flex-1 mr-4 ${getInputClassNames('arrival_at_date')}`}
                            />

                            <Input
                                id='arrival_at_time'
                                onChange={handleFormDataChange}
                                type="time"
                                value={flightData.arrival_at_time}
                                name="arrival_at_time"
                                classNames={getInputClassNames('arrival_at_time')}
                            />
                        </div>

                        { getFormErrors('arrival_at').map(error => <Error key={error}>{error}</Error>) }
                        { getFormErrors('arrival_at_date').map(error => <Error key={error}>{error}</Error>) }
                        { getFormErrors('arrival_at_time').map(error => <Error key={error}>{error}</Error>) }
                    </InputContainer>
                </form>
            </Modal>

            {/* Delete modal */}
            <Modal
                title="Delete flight"
                submitBtnLabel="Delete"
                submitBtnOnclick={() => deleteFlight(setShowDeleteModal)}
                closeBtnOnclick={() => setShowDeleteModal(show => (!show))}
                show={showDeleteModal}
            >
                <p>{deleteFlightMessageRef.current}</p>
            </Modal>
        </>
    );
}

if(document.getElementById('flights_root')){
    createRoot(document.getElementById('flights_root')).render(<Flights />)
}