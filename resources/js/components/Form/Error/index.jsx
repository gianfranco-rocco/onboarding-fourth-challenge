import React from "react";

export default function Error({children}) {
    return (
        <p className="mt-2 text-sm text-red-600 dark:text-red-500 error-message">{children}</p>
    );
}