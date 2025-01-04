import { WEB, API } from './api';

export const getCSRF = async () => {
    return WEB.get(`/sanctum/csrf-cookie`);
};

export const login = async (credentials: any) => {
    try {
        await WEB.get(`/sanctum/csrf-cookie`);
        const response = await API.post('/login', credentials);
        return Promise.resolve(response);
    } catch (error) {
        return Promise.reject(error);
    }
};

export const register = async (credentials: any) => {
    try {
        const response = await API.post('/register', credentials);
        return Promise.resolve(response);
    } catch (error) {
        return Promise.reject(error);
    }
};

export const logout = async () => {
    try {
        const response = await API.delete('/logout');
        const { data } = response;
        return Promise.resolve({ response: data });
    } catch (error) {
        return Promise.reject({ error });
    }
};

export const authenticate = async (token: string | null) => {
    try {
        return await API.get('/auth', {
            headers: {
                // Authorization: `bearer ${token}`, //This is the only place i use bearer but the entire spa app uses cookies
            },
        });
    } catch (error) {
        return Promise.reject({ error });
    }
};
