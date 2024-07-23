import axios from "axios";

const API = axios.create({
    baseURL: "/api",
    headers: {
        "Accept": "application/json",
        "Content-Type": "application/json",
    },
});

export function apiGET(url) {
    return API.get(url);
}

export function apiPost(url, data) {
    return API.post(url, data);
}