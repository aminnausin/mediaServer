import axios from "axios";
axios.defaults.withCredentials = true

export const API = axios.create({
    baseURL: "/api",
    headers: {
        "Accept": "application/json",
        "Content-Type": "application/json",
    },
});

export const WEB = axios.create({
    baseURL: "/",
    headers: {
        "Accept": "application/json",
        "Content-Type": "application/json",
    },
})