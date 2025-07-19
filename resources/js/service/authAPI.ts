import type { ChangeEmailRequest, ChangePasswordRequest, PasswordRequest } from '@/types/requests';

import { useQueryClient } from '@tanstack/vue-query';
import { queryClient } from '@/service/vue-query';
import { WEB, API } from '@/service/api';

export const getCSRF = async () => {
    return WEB.get(`/sanctum/csrf-cookie`);
};

export const login = async (credentials: { email: string; password: string; remember: boolean }) => {
    try {
        await WEB.get(`/sanctum/csrf-cookie`);
        return API.post('/login', credentials);
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
                'X-Skip-Toast': 'true',
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

export async function signOutOtherSessions(data: PasswordRequest) {
    const response = await API.delete('/settings/sessions', { data });

    await queryClient.invalidateQueries({ queryKey: ['sessions'] });

    return response;
}

export function useSignOutOtherSessions() {
    const queryClient = useQueryClient();

    return async function signOutOtherSessions(data: PasswordRequest) {
        const response = await API.delete('/settings/sessions', { data });

        // Manually invalidate the query cache after the request
        await queryClient.invalidateQueries({ queryKey: ['sessions'] });

        return response;
    };
}

export function deleteAccount(data: PasswordRequest) {
    return API.delete('/settings/account', {
        data,
    });
}
