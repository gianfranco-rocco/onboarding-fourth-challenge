import React, { useEffect } from 'react';
import { createRoot } from 'react-dom/client'
import Table from './components/Table';
import Paginator from './components/Table/Paginator';
import useFlights from './hooks/useFlights';
import FlightItem from './components/Table/FlightItem';

export default function Flights() {
    const tableId = 'flightsTable';
    const {
        flights,
        paginator,
        getFlights,
        params,
        handlePagination
    } = useFlights();

    useEffect(() => {
        getFlights();
    }, [params]);

    return(
        <>
            <Table id={tableId}>
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
                <tbody id={`${tableId}Tbody`}>
                    {
                        flights.length ?
                        flights.map(flight => (
                            <FlightItem key={flight.id} flight={flight} />
                        )) :
                        <tr>
                            <td colSpan={7} className='text-center'>No flights available</td>
                        </tr>
                    }
                </tbody>
            </Table>

            <Paginator paginator={paginator} handlePagination={handlePagination} />
        </>
    );
}

if(document.getElementById('flights_root')){
    createRoot(document.getElementById('flights_root')).render(<Flights />)
}