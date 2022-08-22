import axios from "axios";
import { useState } from "react";
import useAPI from "./useAPI";
import useReactToastify from "./useReactToastify";

const useCities = () => {
    const {
        CITIES_API_URI
    } = useAPI();

    const {
        ERROR_TOAST,
        renderToast
    } = useReactToastify();

    const [cities, setCities] = useState([]);
    const [departureCities, setDepartureCities] = useState([]);
    const [destinationCities, setDestinationCities] = useState([]);

    const getCities = async () => {
        await axios
            .get(CITIES_API_URI)
            .then(response => {
                setCities(response.data);
            })
            .catch(({response: {data}}) => {
                renderToast(data.message, ERROR_TOAST);
            });
    }

    const getDepartureCities = async (airline) => {
        await axios
            .get(`${CITIES_API_URI}/${airline}/cities`, {
                params: {
                    airline
                }
            })
            .then(response => {
                setDepartureCities(response.data)
            })
            .catch(({response: {data}}) => {
                renderToast(data.message, ERROR_TOAST);
            });
    }
    
    const getDestinationCities = (departureCityId) => {
        const filteredCities = departureCities.filter(city => city.id != departureCityId);
    
        setDestinationCities(filteredCities);
    }

    return {
        cities,
        departureCities,
        destinationCities,
        getCities,
        getDepartureCities,
        getDestinationCities,
        setDepartureCities,
        setDestinationCities,
    };
}

export default useCities;