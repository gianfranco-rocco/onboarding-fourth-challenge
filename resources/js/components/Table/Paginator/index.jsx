import React from 'react';

export default function Paginator({ paginator = {}, handlePagination }) {
    const spanClassNames = "relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md";
    const anchorClassNames = "relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150";

    return (
        Object.keys(paginator).length
        ?
        <nav role="navigation" aria-label="Pagination Navigation" className="flex justify-between">
            {
                !paginator.prev_cursor
                ?
                <span className={spanClassNames}>
                    « Previous
                </span>
                :
                <button onClick={() => handlePagination('prev')} href={ paginator.prev_page_url } rel="prev" className={anchorClassNames}>
                    « Previous
                </button>
            }
    
            {
                paginator.next_cursor
                ?
                <button onClick={() => handlePagination('next')} href={ paginator.next_page_url } rel="next" className={anchorClassNames}>
                    Next »
                </button>
                :
                <span className={spanClassNames}>
                    Next »
                </span>
            }
        </nav>
        :
        <></>
    );
}