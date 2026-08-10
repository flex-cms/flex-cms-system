import axios from "axios";

let activeRequests = 0;

function showLoader() {
    activeRequests++;

    if (activeRequests === 1) {
        window.dispatchEvent(new CustomEvent("axios-loading-start"));
    }
}

function hideLoader() {
    activeRequests = Math.max(0, activeRequests - 1);

    if (activeRequests === 0) {
        window.dispatchEvent(new CustomEvent("axios-loading-end"));
    }
}

axios.interceptors.request.use(
    (config) => {
        if (!config.skipGlobalLoader) {
            showLoader();
        }

        return config;
    },
    (error) => {
        hideLoader();
        return Promise.reject(error);
    },
);

axios.interceptors.response.use(
    (response) => {
        if (!response.config.skipGlobalLoader) {
            hideLoader();
        }

        return response;
    },
    (error) => {
        if (!error.config?.skipGlobalLoader) {
            hideLoader();
        }

        return Promise.reject(error);
    },
);

window.axios = axios;

export default axios;
