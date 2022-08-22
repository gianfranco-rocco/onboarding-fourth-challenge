import React from "react";
import Button from "../../Button";

export default function FlightItem({ flight, handleFlightEdit }) {
    const {id, airline, departure_city, departure_at, destination_city, arrival_at} = flight;

    const now = new Date();
    const formattedDepartureAt = new Date(departure_at);
    const formattedArrivalAt = new Date(arrival_at);

    /**
     * Only flights which departure at date is before the current timestamp should be
     * able to be modified
     */
    const disabled = now.getTime() > formattedDepartureAt.getTime();

    return (
        <tr>
            <td>{ id }</td>
            <td>{ airline.name }</td>
            <td>{ departure_city.name }</td>
            <td>{ formattedDepartureAt.toLocaleString() }</td>
            <td>{ destination_city.name }</td>
            <td>{ formattedArrivalAt.toLocaleString() }</td>
            <td>
                <Button onClick={() => handleFlightEdit(id)} disabled={disabled} >Edit</Button>
                <Button>Delete</Button>
            </td>
        </tr>
    );
}