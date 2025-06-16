import { toast } from '@/service/toaster/toastService';

import nProgress from 'nprogress';
import axios, { AxiosError } from 'axios';

let progressTimeout: NodeJS.Timeout;

const handleResponse = (response: any) => {
    clearTimeout(progressTimeout);
    nProgress.done(true);
    return response;
};

const handleError = (error: AxiosError<{ message?: string }>) => {
    clearTimeout(progressTimeout);
    nProgress.done(true);

    // if the server throws an error (404, 500 etc.)
    console.log(error);
    const message = error.response?.data?.message ?? error.message;

    toast('Error', { type: 'danger', description: message });
    if (error.status === 401 || error.status == 422 || error.status == 500 || error.status == 404) throw error;

    return error.response;
};

export const API = axios.create({
    baseURL: '/api',
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    },
    withCredentials: true,
});

export const WEB = axios.create({
    baseURL: '/',
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    },
    withCredentials: true,
});

export async function getMediaUrl(path: string): Promise<string> {
    if (!path) return '';
    const newPath = path.replace('storage/', '');

    const response = await axios.get(`/signed-url/${newPath}`);
    return response.data; // The signed URL
}

API.interceptors.request.use((config) => {
    clearTimeout(progressTimeout);
    nProgress.done(true);

    progressTimeout = setTimeout(() => nProgress.start(), 100);

    return config;
});

API.interceptors.response.use(handleResponse, handleError);
WEB.interceptors.response.use(handleResponse, handleError);
