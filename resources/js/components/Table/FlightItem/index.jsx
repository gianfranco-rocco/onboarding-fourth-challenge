import React from "react";
import Button from "../../Button";

export default function FlightItem({flight}) {
    const {id, airline, departure_city, departure_at, destination_city, arrival_at} = flight;

    const formattedDepartureAt = new Date(departure_at).toLocaleString();
    const formattedArrivalAt = new Date(arrival_at).toLocaleString();

    return (
        <tr>
            <td>{ id }</td>
            <td>{ airline.name }</td>
            <td>{ departure_city.name }</td>
            <td>{ formattedDepartureAt }</td>
            <td>{ destination_city.name }</td>
            <td>{ formattedArrivalAt }</td>
            <td>
                <Button>Edit</Button>
                <Button>Delete</Button>
            </td>
        </tr>
    );
}