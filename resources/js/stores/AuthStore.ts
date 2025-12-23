import type { UserResource } from '@/types/resources';

import { authenticate } from '@/service/authAPI';
import { defineStore } from 'pinia';
import { AxiosError } from 'axios';
import { toast } from '@aminnausin/cedar-ui';
import { ref } from 'vue';

// This code is not good
export const useAuthStore = defineStore('Auth', () => {
    const userData = ref<null | UserResource>(null);
    const isLoadingUserData = ref<boolean>(false);

    let userFetchPromise: Promise<boolean> | null = null;

    const fetchUser = async (force: boolean = false): Promise<boolean> => {
        /*
            Auth States:

            1: Never logged in -> No token -> state is null
            2: Logged in previously -> Token -> Token is invalid (ajax) -> State is false
            3: Logged in previously -> Token -> Token is valid (ajax) -> State is true
            4: State exists -> State is State (Logged in or out has already been checked)

        */

        if (!localStorage.getItem('auth-token')) return false; // no auth token

        if (userData.value && !force) return true;

        if (userFetchPromise) return userFetchPromise;

        userFetchPromise = (async () => {
            isLoadingUserData.value = true;

            try {
                const localToken = localStorage.getItem('auth-token');
                if (!localToken) {
                    clearAuthState();
                    return false;
                }

                const { data, status } = await authenticate(localToken);

                if (status !== 200) {
                    throw new AxiosError('Not Authenticated', status.toString());
                }

                userData.value = data.data?.user;
                return true;
            } catch (error) {
                clearAuthState();

                console.error('Authentication failed:', error);
                return false;
            } finally {
                isLoadingUserData.value = false;
                userFetchPromise = null;
            }
        })();

        return userFetchPromise;
    };

    const clearAuthState = (showMessage: boolean = false, status = 401): void => {
        userData.value = null;
        isLoadingUserData.value = false;
        localStorage.removeItem('auth-token');

        if (!showMessage) return;
        const message = status === 419 ? 'Session Expired' : `Authentication Failed (${status})`;

        toast.warning(message, {
            description: 'Please log in again.',
        });
    };

    return {
        userData,
        isLoadingUserData,
        fetchUser,
        clearAuthState,
    };
});
