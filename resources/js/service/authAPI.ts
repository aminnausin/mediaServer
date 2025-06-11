import type { ChangeEmailRequest, ChangePasswordRequest, PasswordRequest } from '@/types/requests';

import { WEB, API } from '@/service/api';

export const getCSRF = async () => {
    return WEB.get(`/sanctum/csrf-cookie`);
};

export const login = async (credentials: any) => {
    try {
        await WEB.get(`/sanctum/csrf-cookie`);
        const response = await API.post('/login', credentials);
        return Promise.resolve(response);
    } catch (error) {
        throw error instanceof Error ? error : new Error(String(error));
    }
};

export function recoverAccount(credentials: { email: string }) {
    return API.post('/recovery', credentials);
}

export function resetPassword(credentials: { token: string; email: string; password: string; password_confirmation: string }) {
    return API.post(`/reset-password/${credentials.token}`, credentials);
}

export const register = async (credentials: any) => {
    try {
        const response = await API.post('/register', credentials);
        return Promise.resolve(response);
    } catch (error) {
        throw error instanceof Error ? error : new Error(String(error));
    }
};

export const logout = async () => {
    try {
        const response = await API.delete('/logout');
        const { data } = response;
        return Promise.resolve({ response: data });
    } catch (error) {
        throw error instanceof Error ? error : new Error(String(error));
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
        throw error instanceof Error ? error : new Error(String(error));
    }
};

export function changePassword(data: ChangePasswordRequest) {
    return API.put(`/settings/password`, data);
}

export function changeEmail(data: ChangeEmailRequest) {
    return API.put(`/settings/email`, data);
}

export function getSessions() {
    return API.get('/settings/sessions');
}

export function signOutOtherSessions(data: PasswordRequest) {
    return API.delete('/settings/sessions', {
        data,
    });
}

export function deleteAccount(data: PasswordRequest) {
    return API.delete('/settings/account', {
        data,
    });
}
