import axios from "axios";
import { useState } from "react";
import useAPI from "./useAPI";
import useReactToastify from "./useReactToastify";

const useAirlines = () => {
    const {
        AIRLINES_API_URI
    } = useAPI();

    const {
        ERROR_TOAST,
        renderToast
    } = useReactToastify();

    const [airlines, setAirlines] = useState([]);

    const getAirlines = () => {
        axios
            .get(AIRLINES_API_URI)
            .then(response => {
                setAirlines(response.data);
            })
            .catch(({response: {data}}) => {
                renderToast(data.message, ERROR_TOAST);
            });
    }

    return {
        airlines,
        getAirlines,
    };
}

export default useAirlines;