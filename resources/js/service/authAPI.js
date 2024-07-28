// TODO: json from response does not include status code. Find a modular way to handle that
import { WEB, API } from './api';

export const getCSRF = async () => {
    return WEB.get(`/sanctum/csrf-cookie`)
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
        const response = await API.post('/register', credentials)
        const { data } = response;
        return Promise.resolve({response: data});
    } catch (error) {
        return Promise.reject({error});
    }
}

export const logout = async () => {
    try {
        const response = await API.delete('/logout');
        const { data } = response;
        return Promise.resolve({response: data});
    } catch (error) {
        return Promise.reject({error});
    }
}

export const authenticate = async (token) => {
    try {
        return await API.get('/auth', { headers: {
            Authorization: `bearer ${token}`
        }});
    } catch (error) {
        return Promise.reject({error});
    }
}