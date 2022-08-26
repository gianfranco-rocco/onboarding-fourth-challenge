import { toast } from "react-toastify";
import 'react-toastify/dist/ReactToastify.css';

const useReactToastify = () => {
    const INFO_TOAST = 'info';
    const SUCCESS_TOAST = 'success';
    const WARNING_TOAST = 'warning';
    const ERROR_TOAST = 'error';
    const DEFAULT_TOAST = 'default';

    const renderToast = (message, toastType) => {
        if (!message || !toastType) {
            return null;
        }

        const config = {
            position: "top-right",
            autoClose: 5000,
            hideProgressBar: false,
            closeOnClick: true,
            pauseOnHover: true,
            draggable: true,
            progress: undefined,
        };

        if (toastType === DEFAULT_TOAST) {
            toast(message, config);
        } else {
            toast[toastType](message, config);
        }
    }

    return {
        INFO_TOAST,
        SUCCESS_TOAST,
        WARNING_TOAST,
        ERROR_TOAST,
        DEFAULT_TOAST,
        renderToast
    };
}

export default useReactToastify;