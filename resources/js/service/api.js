import axios from "axios";

const handleResponse = (response) => {
    return response;
}

const handleError = (error) => {
    // if the server throws an error (404, 500 etc.)
    console.log(error.response.status);
    if(error.response.status === 403 | error.response.status === 500){
        window.location.href = `/${error.response.status}`
        return;
    }
    return error.response;
}

export const API = axios.create({
    baseURL: "/api",
    headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
    },
    withCredentials: true,
});

export const WEB = axios.create({
    baseURL: "/",
    headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
    },
    withCredentials: true,
});

API.interceptors.response.use(handleResponse, handleError);
WEB.interceptors.response.use(handleResponse, handleError);
