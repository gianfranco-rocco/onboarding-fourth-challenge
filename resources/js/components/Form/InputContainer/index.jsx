import React from "react";

export default function InputContainer({ children, classNames = '' }) {
    return <div className={classNames}>{children}</div>;
}