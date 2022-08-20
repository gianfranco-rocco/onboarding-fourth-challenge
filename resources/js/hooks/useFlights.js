import axios from "axios";
import { useState } from "react";

const useFlights = () => {
    const API_URL = 'http://localhost:80/api/flights';

    const [flights, setFlights] = useState([]);
    const [paginator, setPaginator] = useState({});
    const [error, setError] = useState('');
    const [formErrors, setFormErrors] = useState([]);
    const [params, setParams] = useState({
        departureAt: null,
        arrivalAt: null,
        airline: null,
        departureCity: null,
        destinationCity: null,
        cursor: null
    });

    const getFlights = async () => {
        await axios.get(API_URL, {
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
        params,
        setParams,
        handlePagination
    }
}

export default useFlights;