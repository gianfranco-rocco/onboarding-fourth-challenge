import axios from "axios";
import { useState } from "react";
import useAPI from "./useAPI";
import useReactToastify from "./useReactToastify";

const useFlights = () => {
    const HTTP_UNPROCESSABLE = 422;

    const {
        FLIGHTS_API_URI
    } = useAPI();

    const {
        SUCCESS_TOAST,
        ERROR_TOAST,
        renderToast
    } = useReactToastify();

    const [flights, setFlights] = useState([]);
    const [paginator, setPaginator] = useState({});
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
            renderToast(response.response.data.message, ERROR_TOAST);
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

            renderToast(response.data.message, SUCCESS_TOAST);
        })
        .catch(({response}) => {
            if (response.status === HTTP_UNPROCESSABLE) {
                setFormErrors(response.data.errors);
            } else {
                renderToast(response.data.message, ERROR_TOAST);
            }
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