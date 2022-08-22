import React from "react";

export default function Button({ children, onClick, id = '', type = 'button', className = '', disabled = false }) {
    return (
        <button
            id={id}
            type={type}
            onClick={onClick}
            disabled={disabled}
            className={`border rounded-lg px-5 py-2 ${disabled ? 'disabled:opacity-75 bg-gray-300 text-white' : 'hover:bg-blue-700 hover:text-white bg-white'} ${className}`}
        >
            { children }
        </button>
    )
}