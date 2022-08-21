import axios from "axios";
import { useEffect, useState } from "react";
import useAPI from "./useAPI";


const useFlights = () => {
    const {
        FLIGHTS_API_URI
    } = useAPI();

    const [flights, setFlights] = useState([]);
    const [paginator, setPaginator] = useState({});
    const [error, setError] = useState('');
    const [formErrors, setFormErrors] = useState({});
    const [params, setParams] = useState({
        departureAt: null,
        arrivalAt: null,
        airline: null,
        departureCity: null,
        destinationCity: null,
        cursor: null
    });
    const [flightData, setFlightData] = useState({
        airline: '',
        departure_city: '',
        destination_city: '',
        departure_at_date: '',
        departure_at_time: '',
        arrival_at_date: '',
        arrival_at_time: ''
    });

    const getFlights = async () => {
        await axios.get(FLIGHTS_API_URI, {
            params
        })
        .then(response => {
            setPaginator(response.data);
            setFlights(response.data.data);
        })
        .catch(response => {
            setError(response.response.data.message);
        });
    }

    const hasFormErrors = (key) => {
        return getFormErrors(key).length > 0;
    }

    const getFormErrors = (key) => {
        return formErrors[key] || [];
    }

    const saveFlight = async (setShowModal) => {
        await axios.post(FLIGHTS_API_URI, flightData)
        .then(response => {
            getFlights();

            if (setShowModal) {
                setShowModal(show => (!show));
            }
        })
        .catch(response => {
            setFormErrors(response.response.data.errors);
        });
    }

    const handlePagination = (paginationType) => {
        if (Object.keys(paginator).length) {
            let cursor = null;

            switch(paginationType) {
                case 'prev':
                    cursor = paginator.prev_cursor;
                    break;
                case 'next':
                    cursor = paginator.next_cursor;
                    break;
                default:
                    break;
            }

            if (cursor) {
                setParams(currParams => ({
                    ...currParams,
                    cursor
                }));
            }
        }
    }

    return {
        flights,
        paginator,
        getFlights,
        saveFlight,
        params,
        setParams,
        handlePagination,
        flightData,
        setFlightData,
        getFormErrors,
        hasFormErrors
    }
}

export default useFlights;