import React from "react";

export default function Label({ htmlFor, children, classNames = '' }) {
    return (
        <label 
            id={`${htmlFor}-label`}
            htmlFor={htmlFor}
            className={`${classNames} block mb-2 text-sm font-medium`}
        >
            {children}
        </label>
    );
}