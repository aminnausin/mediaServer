import { useAuthStore } from '@/stores/AuthStore';
import { toast } from '@aminnausin/cedar-ui';

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

    const message = error.response?.data?.message ?? error.message;

    const auth = useAuthStore();
    const status = error.response?.status ?? 0;

    // if the server throws an error (404, 500 etc.)
    const knownError = [403, 422, 500, 404, 502, 401, 419].includes(status);
    const isSessionExpired = status === 419;
    const isAuthError = status === 401;
    const showToast = !error.config?.headers?.['X-Skip-Toast'];

    // Handle expired auth token and session timeout (CSRF token expired)
    if ((isAuthError || isSessionExpired) && auth.userData) {
        const router = (await import('@/router')).router;

        const currentRoute = router.currentRoute.value;

        auth.clearAuthState(true, status);

        if (currentRoute.meta?.protected) {
            router.replace({ path: '/' });
        }

        error.message = `Session Expired: ${message}`;
    } else if (showToast && !isAuthError) {
        toast.error('Error', { description: message });
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
