import React from 'react';
import { createRoot } from 'react-dom/client'

export default function Flights() {
    return(
        <>
            <h1>Flights Index</h1>
        </>
    );
}

if(document.getElementById('flights_root')){
    createRoot(document.getElementById('flights_root')).render(<Flights />)
}