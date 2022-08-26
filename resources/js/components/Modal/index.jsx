import React from "react";
import Button from "../Button";

export default function Modal({
    id,
    title,
    children,
    closeBtnOnclick,
    submitBtnLabel,
    submitBtnOnclick,
    closeBtnLabel = 'Cancel',
    show = false,
}) {
    return (
        <>
            {
                show &&
                <div 
                    id={id}
                    tabIndex="-1"
                    aria-modal="true"
                    role="dialog"
                    style={{
                        backgroundColor: 'rgba(0, 0, 0, .4)'
                    }}
                    className="overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 w-full md:inset-0 h-modal md:h-full justify-center items-center flex"
                >
                    <div className="relative p-4 w-full max-w-2xl h-full md:h-auto">
                        <div className="relative bg-white rounded-lg shadow dark:bg-gray-700">
                            <div className="flex justify-between items-start p-4 rounded-t border-b dark:border-gray-600">
                                <h3 id={`${id}Title`} className="text-xl font-semibold text-gray-900 dark:text-white">
                                    { title }
                                </h3>
                                <button type="button" className="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white" onClick={closeBtnOnclick}>
                                    <svg aria-hidden="true" className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd"></path></svg>
                                    <span className="sr-only">Close modal</span>
                                </button>
                                <button className="hidden" id={`${id}ToggleBtn`}></button>
                            </div>

                            <div className="p-6 space-y-6">
                                { children }
                            </div>

                            <div className="flex items-center p-6 space-x-2 rounded-b border-t border-gray-200 dark:border-gray-600">
                                <Button id={`${id}SubmitBtn`} className="bg-blue-700 text-white hover:opacity-80" onClick={submitBtnOnclick}>{submitBtnLabel}</Button>
                                <Button onClick={closeBtnOnclick}>{closeBtnLabel}</Button>
                            </div>
                        </div>
                    </div>
                </div>
            }
        </>
    );
}