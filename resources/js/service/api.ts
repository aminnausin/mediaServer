import { useAuthStore } from '@/stores/AuthStore';
import { toast } from '@/service/toaster/toastService';

import axios, { AxiosError } from 'axios';
import nProgress from 'nprogress';

let progressTimeout: NodeJS.Timeout;

const handleResponse = (response: any) => {
    clearTimeout(progressTimeout);
    nProgress.done(true);
    return response;
};

const handleError = async (error: AxiosError<{ message?: string }>) => {
    clearTimeout(progressTimeout);
    nProgress.done(true);

    const auth = useAuthStore();
    const message = error.response?.data?.message ?? error.message;
    const status = error.response?.status ?? 0;

    // if the server throws an error (404, 500 etc.)
    const knownError = [401, 422, 500, 404, 419].includes(status);
    const showToast = !error.config?.headers?.['X-Skip-Toast'];
    const isSessionExpired = status === 419;
    const isAuthError = status === 401;

    if (showToast) {
        toast('Error', { type: 'danger', description: message });
    }

    // Handle session timeout (CSRF token expired)
    if (isSessionExpired && auth.userData) {
        import('@/router/index').then(({ router }) => {
            if (router.currentRoute.value.name !== 'logout') {
                router.push('/logout');
            }
        });
        error.message = `Session Expired: ${message}`;
        throw error;
    }

    // Handle expired auth token
    if (isAuthError && auth.userData) {
        auth.clearAuthState(true);
        error.message = `Not logged in: ${message}`;
        throw error;
    }

    if (!knownError) {
        console.error(error);
        return error.response;
    }

    throw error;
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
