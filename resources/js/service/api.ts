import { toast } from '@/service/toaster/toastService';

import axios from 'axios';

const handleResponse = (response: any) => {
    return response;
};

const handleError = (error: { response: { status: number; data: { message: any } }; message: any }) => {
    // if the server throws an error (404, 500 etc.)
    console.log(error);
    // if (error.response.status === 403) {
    //     //|| error.response.status === 500
    //     window.location.href = `/${error.response.status}?message=${error?.response?.data?.message ?? error?.message}`;
    //     return;
    // }

    toast('Error', { type: 'danger', description: error.response.data.message ?? error.message });
    if (error.response.status === 401 || error.response.status == 422 || error.response.status == 500 || error.response.status == 404) throw error;

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
    // console.log(newPath);

    const response = await axios.get(`/signed-url/${newPath}`);
    return response.data; // The signed URL
}

API.interceptors.response.use(handleResponse, handleError);
WEB.interceptors.response.use(handleResponse, handleError);
