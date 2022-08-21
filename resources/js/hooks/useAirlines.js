import axios from "axios";
import { useState } from "react";
import useAPI from "./useAPI";

const useAirlines = () => {
    const {
        AIRLINES_API_URI
    } = useAPI();

    const [airlines, setAirlines] = useState([]);

    const getAirlines = () => {
        axios
            .get(AIRLINES_API_URI)
            .then(response => {
                setAirlines(response.data);
            })
            .catch(response => {
                console.log('airlines response error', response)
            });
    }

    return {
        airlines,
        getAirlines,
    };
}

export default useAirlines;