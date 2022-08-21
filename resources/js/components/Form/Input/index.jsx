import React from "react";

export default function Input({
    name,
    onChange,
    id = '',
    type = 'text',
    placeholder = '',
    value = '',
    classNames = '',
}) {
    return (
        <input 
            id={id}
            type={type}
            name={name}
            placeholder={placeholder}
            defaultValue={value}
            className={`${classNames} border text-sm rounded-lg p-2.5 dark:bg-red-100`}
            onChange={onChange}
        />
    );
}