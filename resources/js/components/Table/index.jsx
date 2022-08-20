import React from 'react';
import './index.css';

export default function Table({ id, children }) {
    return(
        <>
            <table 
                id={id}
                className="table table-fixed border-spacing-2 border-collapse border border-slate-500 w-full mb-6">
                { children }
            </table>
        </>
    );
}