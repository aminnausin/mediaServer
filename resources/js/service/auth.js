// TODO: json from response does not include status code. Find a modular way to handle that
import axios from "axios";
import { WEB, API } from './api';

export const getCSRF = async () => {
    return axios.get(`/sanctum/csrf-cookie`)
}

export const login = async (credentials) => {
    try {
        await WEB.get(`/sanctum/csrf-cookie`);

        const response = await API.post('/login', credentials);
        const { data } = response;
        return Promise.resolve({response: data});
    } catch (error) {
        return Promise.reject({error});
    }
};

export const register = async (credentials) => {
    try {
        // const response = await fetch(`/api/register`, {
        //     method: 'post',
        //     headers: {
        //         'Accept': 'application/json',
        //         'Content-Type': 'application/json'
        //     },
        //     body: JSON.stringify({
        //         ...credentials
        //     })
        // })
        const response = await API.post('/register', credentials)
        const { data } = response;
        return Promise.resolve({response: data});
    } catch (error) {
        return Promise.reject({error});
    }
}

export const logout = async () => {
    try {
        // const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        // const localToken = localStorage.getItem('auth-token');
        // const response = await fetch(`/api/logout`, {
        //     method: 'delete',
        //     headers: {
        //         'Accept': 'application/json',
        //         'Content-Type': 'application/json',
        //         'X-CSRF-TOKEN': csrfToken
        //         // 'Authorization': "Bearer " + localToken,
        //     }
        // })
        const response = await API.delete('/logout');
        const { data } = response;
        return Promise.resolve({response: data});
    } catch (error) {
        return Promise.reject({error});
    }
}