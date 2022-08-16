<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="favicon.svg" type="image/svg">
    <title>
        {{ config('app.name') }}

        @hasSection('title')
            - @yield('title')
        @endif
    </title>
    @vite('resources/css/app.css')
    @vite('resources/css/header.css')
    @vite('resources/css/button.css')
    @yield('styles')
    <link rel="stylesheet" href="https://unpkg.com/flowbite@1.5.2/dist/flowbite.min.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <header class="flex flex-col sm:flex-row sm:justify-between px-6 sm:px-10 py-4 header-nav drop-shadow-sm">
        <div class="flex justify-center sm:justify-start mb-4 sm:mb-0">
            <a href="/" class="flex items-center">
                <svg class="header-icon mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
                    <path d="M482.3 192C516.5 192 576 221 576 256C576 292 516.5 320 482.3 320H365.7L265.2 495.9C259.5 505.8 248.9 512 237.4 512H181.2C170.6 512 162.9 501.8 165.8 491.6L214.9 320H112L68.8 377.6C65.78 381.6 61.04 384 56 384H14.03C6.284 384 0 377.7 0 369.1C0 368.7 .1818 367.4 .5398 366.1L32 256L.5398 145.9C.1818 144.6 0 143.3 0 142C0 134.3 6.284 128 14.03 128H56C61.04 128 65.78 130.4 68.8 134.4L112 192H214.9L165.8 20.4C162.9 10.17 170.6 0 181.2 0H237.4C248.9 0 259.5 6.153 265.2 16.12L365.7 192H482.3z"/>
                </svg>

                <span class="header-app-name">{{ config('app.name') }}</span>
            </a>
        </div>

        <nav class="flex items-center justify-center sm:justify-end">
            <ul class="flex">
                <li>
                    <a 
                        href="{{ route('cities.index') }}" 
                        @class([
                            'header-active' => Route::is('cities.*') 
                        ])
                    >
                        Cities
                    </a>
                </li>
                <li>
                    <a 
                        href="#" 
                        @class([
                            "ml-8",
                            'header-active' => Route::is('airlines.index')
                        ])
                    >
                        Airlines
                    </a>
                </li>
                <li>
                    <a 
                        href="#" 
                        @class([
                            "ml-8",
                            'header-active' => Route::is('home') || Route::is('flights.*')
                        ])
                    >
                        Flights
                    </a>
                </li>
            </ul>
        </nav>
    </header>

    <main class="px-6 sm:px-10 py-4">
        <h1 class="mb-4 page-title">@yield('title')</h1>

        @yield('body')
    </main>

    <script src="https://unpkg.com/flowbite@1.5.2/dist/flowbite.js"></script>

    <script type="module">
        const inputErrorClasses = 'bg-red-50 border-red-500 text-red-900 placeholder-red-700 focus:ring-red-500 focus:border-red-500 dark:border-red-400';
        const labelErrorClasses = 'text-red-700 dark:text-red-500';

        const displayFormErrorsFromResponse = (response, formId) => {
            for (const [key, errors] of Object.entries(response.responseJSON.errors)) {
                const inputId = `${formId}-${key}`;

                enableErrorClasses(inputId);
                errors.forEach(error => appendErrorMessage(inputId, error));
            }
        }

        const enableErrorClasses = (inputId) => {
            $(`#${inputId}`).addClass(inputErrorClasses);
            $(`#${inputId}-label`).addClass(labelErrorClasses);
        }

        const disableErrorsFromForm = (formId) => {
            const formElements = document.getElementById(formId).elements;

            Array.from(formElements).forEach(({id: inputId}) => {
                $(`#${inputId}`).removeClass(inputErrorClasses);
                $(`#${inputId}-label`).removeClass(labelErrorClasses);
            });
        }

        const appendErrorMessage = (inputId, message) => {
            $(`#${inputId}-container`).append(`<p class="mt-2 text-sm text-red-600 dark:text-red-500 error-message">${message}</p>`);
        }

        const removeErrorMessages = () => {
            $(".error-message").remove();
        }

        const clearErrorsFromForm = (formId) => {
            removeErrorMessages();
            disableErrorsFromForm(formId);
        }

        const toggleModal = (modalId) => {
            $(`#${modalId}ToggleBtn`).click();
        }

        const setInputValue = (inputId, value) => {
            $(`#${inputId}`).val(value);
        }

        Object.assign(window, {
            displayFormErrorsFromResponse,
            removeErrorMessages,
            disableErrorsFromForm,
            clearErrorsFromForm,
            toggleModal,
            setInputValue
        });
    </script>

    @yield('scripts')
</body>
</html>