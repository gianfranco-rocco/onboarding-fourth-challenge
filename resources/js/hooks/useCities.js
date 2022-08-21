import axios from "axios";
import { useEffect, useState } from "react";
import useAPI from "./useAPI";

const useCities = () => {
    const {
        CITIES_API_URI
    } = useAPI();

    const [cities, setCities] = useState([]);
    const [departureCities, setDepartureCities] = useState([]);
    const [destinationCities, setDestinationCities] = useState([]);

    useEffect(() => {
        setDestinationCities([]);
    }, [departureCities]);

    const getCities = async () => {
        await axios
            .get(CITIES_API_URI)
            .then(response => {
                setCities(response.data);
            })
            .catch(response => {
                console.log('cities response error', response);
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
            .catch(response => {
                console.log('departure cities response error', response);
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
        setDestinationCities,
        getDestinationCities,
    };
}

export default useCities;