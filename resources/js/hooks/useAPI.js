const useAPI = () => {
    const BASE_API_URI = `http://localhost:80/api`;
    const AIRLINES_API_URI = `${BASE_API_URI}/airlines`;
    const CITIES_API_URI = `${BASE_API_URI}/cities`;
    const FLIGHTS_API_URI = `${BASE_API_URI}/flights`;

    return {
        BASE_API_URI,
        AIRLINES_API_URI,
        CITIES_API_URI,
        FLIGHTS_API_URI
    }
}

export default useAPI;