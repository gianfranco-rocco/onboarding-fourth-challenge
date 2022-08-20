import React from "react";

export default function Button({ children, onClick, id = '', type = 'button', className = '' }) {
    return (
        <button
            id={id}
            type={type}
            onClick={onClick}
            className={`hover:bg-blue-700 border rounded-lg px-5 py-2 hover:text-white bg-white ${className}`}
        >
            { children }
        </button>
    )
}