import { useAuthStore } from '@/stores/AuthStore';
import { getCSRF } from '@/service/authAPI';
import { toast } from '@aminnausin/cedar-ui';

import axios, { AxiosError, type AxiosResponse, type InternalAxiosRequestConfig } from 'axios';
import nProgress from 'nprogress';

// For progress bar
let progressTimeout: NodeJS.Timeout;

// For csrf handling
let csrfRefreshPromise: Promise<AxiosResponse> | null = null;
let isRefreshing = false;
let queue: Array<(tokenReady: boolean) => void> = []; // A queue of promises that are conditionally called after attempt at refreshing csrf

function refreshCsrf() {
    csrfRefreshPromise ??= getCSRF().finally(() => {
        csrfRefreshPromise = null;
    });

    return csrfRefreshPromise;
}

function queueRequest(cb: (ready: boolean) => void) {
    queue.push(cb);
}

function flushQueue(success: boolean) {
    queue.forEach((cb) => cb(success));
    queue = [];
}

const handleResponse = (response: any) => {
    clearTimeout(progressTimeout);
    nProgress.done(true);
    return response;
};

const handleError = async (error: AxiosError<{ message?: string }>) => {
    clearTimeout(progressTimeout);
    nProgress.done(true);

    const auth = useAuthStore();
    const status = error.response?.status ?? 0;
    const config = error.config;
    const message = error.response?.data?.message ?? error.message;
    const showToast = !config?.headers?.['X-Skip-Toast'];

    // if the server throws an error (404, 500 etc.)
    const isSessionExpired = status === 419;
    const isAuthError = status === 401;

    // If 419 (CSRF token expired) and not already marked, manually refresh csrf and retry the request after marking it
    if (isSessionExpired && config && !config._retried) {
        if (isRefreshing) {
            return new Promise((resolve, reject) => {
                queueRequest((ready) => {
                    if (!ready) return reject(new AxiosError('CSRF refresh failed'));
                    config._retried = true;
                    resolve(API.request(config));
                });
            });
        }

        config._retried = true;
        isRefreshing = true;

        try {
            await refreshCsrf();
            flushQueue(true);
            return API.request(config);
        } catch (e) {
            flushQueue(false);
            throw e; // Session Expired and Irrecoverable
        } finally {
            isRefreshing = false;
        }
    }

    // Handle expired auth
    if (isAuthError && auth.userData) {
        auth.clearAuthState(true, status);

        const router = (await import('@/router')).router;
        if (router.currentRoute.value.meta?.protected) {
            router.replace({ path: '/' });
        }

        throw error;
    }

    // Show error for non 401/419 errors
    if (showToast && !isAuthError && !isSessionExpired) {
        toast.error('Error', { description: message });
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
