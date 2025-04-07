import type { UserResource } from '@/types/resources';

import { AxiosError, isAxiosError } from 'axios';
import { authenticate } from '@/service/authAPI';
import { defineStore } from 'pinia';
import { toast } from '@/service/toaster/toastService';
import { ref } from 'vue';

// This code is not good
export const useAuthStore = defineStore('Auth', () => {
    const userData = ref<null | UserResource>(null);

    const auth = async (): Promise<boolean> => {
        /*
            Auth States:

            1: Never logged in -> No token -> state is null
            2: Logged in previously -> Token -> Token is invalid (ajax) -> State is false
            3: Logged in previously -> Token -> Token is valid (ajax) -> State is true
            4: State exists -> State is State (Logged in or out has already been checked)

        */

        if (!localStorage.getItem('auth-token')) return false; // console.log('no auth token');

        if (userData.value === null && !localStorage.getItem('auth-token')) return false; // console.log('never logged in'); this is never triggered because x, and y after an if statement checking for y will never apply

        try {
            const localToken = localStorage.getItem('auth-token');

            if (!localToken) {
                clearAuthState();
                return false;
            }

            const { data, status } = await authenticate(localToken);

            if (status !== 200) {
                // Auth request was denied (so local data is invalid) -> don't logout because that will be another 401 anyway
                throw new AxiosError('Not Authenticated', status.toString());
            }

            userData.value = data.data.user;
            return true;
        } catch (error: unknown) {
            console.error('Authentication failed:', error);

            let message = 'Authentication Error';
            if (isAxiosError(error)) {
                message = error.response?.status === 401 ? 'Session Expired' : `Authentication Failed (${error.response?.status})`;
            }

            toast.add(message, {
                type: 'warning',
                description: 'Please log in again.',
            });

            toast.add('Session Expired', { type: 'warning', description: `Please log in again.` });
            clearAuthState();
            return false;
        }
    };

    const clearAuthState = (): void => {
        userData.value = null;
        localStorage.removeItem('auth-token');
    };

    return {
        userData,
        auth,
        clearAuthState,
    };
});
