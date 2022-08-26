import React from "react";

export default function Dropdown({
    id,
    name,
    onChange,
    classNames,
    noOptionsLabel,
    value = '',
    defaultOptionLabel = 'Choose',
    options = [],
    disabled = false,
    withDefaultOption = true,
    defaultOptionDisabled = true
}) {
    return (
        <select 
            id={id}
            name={name}
            onChange={onChange}
            disabled={disabled}
            value={value}
            className={`${classNames} text-base focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg py-2 inline-flex items-center dark:focus:ring-blue-800`}
        >
            {
                !options.length 
                ?
                <option value="" disabled>{noOptionsLabel}</option> 
                :
                <>
                    {withDefaultOption && <option value="" disabled={defaultOptionDisabled}>{defaultOptionLabel}</option>}

                    {
                        options.map(({id, name}) => (
                            <option key={id} value={id}>{name}</option>
                        ))
                    }
                </>
            }
        </select>
    );
}