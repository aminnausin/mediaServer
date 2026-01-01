import type { ChangeEmailRequest, ChangePasswordRequest, PasswordRequest } from '@/types/requests';
import type { AxiosResponse } from 'axios';
import type { UserResource } from '@/types/resources';

import { useQueryClient } from '@tanstack/vue-query';
import { queryClient } from '@/service/vue-query';
import { WEB, API } from '@/service/api';

export const getCSRF = async () => {
    return WEB.get(`/sanctum/csrf-cookie`);
};

export const login = async (credentials: { email: string; password: string; remember: boolean }) => {
    await getCSRF();
    return API.post('/login', credentials);
};

export function recoverAccount(credentials: { email: string }) {
    return API.post('/recovery', credentials);
}

export function resetPassword(credentials: { token: string; email: string; password: string; password_confirmation: string }) {
    return API.post(`/reset-password/${credentials.token}`, credentials);
}

export const register = async (credentials: any) => {
    return API.post('/register', credentials);
};

export const logout = async () => {
    const response = await API.delete('/logout');
    return Promise.resolve({ response: response.data });
};

export const authenticate = async (): Promise<AxiosResponse<{ user: UserResource | null; isAuthenticated: boolean }>> => {
    return API.get('/auth', {
        headers: {
            'X-Skip-Toast': 'true',
            // Authorization: `bearer ${token}`, //This is the only place i use bearer but the entire spa app uses cookies
        },
    });
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

// What is that?  ???
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
