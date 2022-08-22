import axios from "axios";
import { useEffect, useState } from "react";
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
        departure_at: '',
        arrival_at: '',
        airline: null,
        departure_city: '',
        destination_city: '',
        cursor: null
    });
    const [flightData, setFlightData] = useState({
        id: '',
        airline: '',
        departure_city: '',
        destination_city: '',
        departure_at_date: '',
        departure_at_time: '',
        arrival_at_date: '',
        arrival_at_time: ''
    });

    useEffect(() => {
        getFlights()
    }, [params]);

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

    const getFlight = async (flightId) => {
        return await axios
        .get(`${FLIGHTS_API_URI}/${flightId}`)
        .then(({data}) => {
            return data.data;
        })
        .catch(response => {
            renderToast(response.response.data.message, ERROR_TOAST);
        });
    }

    const updateFlight = async (setShowModal) => {
        await axios
        .put(`${FLIGHTS_API_URI}/${flightData.id}`, flightData)
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

    const deleteFlight = async (setShowDeleteModal) => {
        await axios
        .delete(`${FLIGHTS_API_URI}/${flightData.id}`)
        .then(({data}) => {
            getFlights();

            if (setShowDeleteModal) {
                setShowDeleteModal(show => (!show));
            }

            renderToast(data.message, SUCCESS_TOAST);
        })
        .catch(response => {
            renderToast(response.response.data.message, ERROR_TOAST);
        });
    }

    const handleFiltering = (e) => {
        setParams(currParams => ({
            ...currParams,
            [e.target.name]: e.target.value
        }));
    }

    const hasFormErrors = (key) => {
        return getFormErrors(key).length > 0;
    }

    const getFormErrors = (key) => {
        return formErrors[key] || [];
    }

    const removeFormErrors = (key) => {
        if (hasFormErrors(key)) {
            delete formErrors[key];
        }
    }

    const handlePagination = (paginationType) => {
        if (Object.keys(paginator).length) {
            const cursor = paginator[`${paginationType}_cursor`];

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
        getFlight,
        updateFlight,
        deleteFlight,
        params,
        setParams,
        handlePagination,
        flightData,
        setFlightData,
        getFormErrors,
        hasFormErrors,
        setFormErrors,
        removeFormErrors,
        handleFiltering
    }
}

export default useFlights;