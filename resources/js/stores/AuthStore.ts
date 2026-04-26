import type { UserResource } from '@/types/resources';

import { computed, ref } from 'vue';
import { authenticate } from '@/service/authAPI';
import { defineStore } from 'pinia';
import { toast } from '@aminnausin/cedar-ui';
import { API } from '@/service/api';

// This code is not good
export const useAuthStore = defineStore('Auth', () => {
    const userData = ref<null | UserResource>(null);
    const isLoadingUserData = ref<boolean>(false);

    const guestToken = ref<string | null>(localStorage.getItem('guest_token'));

    let userFetchPromise: Promise<boolean> | null = null;

    const initGuestToken = async () => {
        if (isAuthenticated.value || guestToken.value) return;

        const { data } = await API.post('/guest-token');

        guestToken.value = data.token;
        localStorage.setItem('guest_token', data.token);
    };

    const clearGuestToken = () => {
        guestToken.value = null;
        localStorage.removeItem('guest_token');
    };

    const fetchUser = async (force: boolean = false): Promise<boolean> => {
        /*
            Auth States (not very good):

            1: Initial -> No checks have run -> State is null
            2: Never logged in -> No State -> State is null
            3: State exists -> State is State (Logged in or out has already been checked)

        */

        if (userData.value?.id && !force) return true; // Bad practice? Should I always check?
        if (userFetchPromise) return userFetchPromise;

        userFetchPromise = (async () => {
            isLoadingUserData.value = true;

            try {
                const { data } = await authenticate();

                if (data.isAuthenticated) {
                    userData.value = data.user;
                    clearGuestToken();
                    return true;
                }

                clearAuthState(false);
                await initGuestToken();
                return false;
            } catch (error) {
                // Only when network or server error
                console.error(error);
                clearAuthState(true);
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
        localStorage.removeItem('auth-token'); // Legacy: Clears existing auth-tokens. No auth-tokens are created ever again.

        if (!showMessage) return;
        const message = 'Login Expired' + (status ? ` ${status}` : '');

        // Can remove?
        toast.warning(message, {
            description: 'Please log in again.',
        });
    };

    const isAuthenticated = computed(() => !!userData.value?.id);
    const isAdmin = computed(() => userData.value?.is_admin);

    return {
        userData,
        isAuthenticated,
        isAdmin,
        isLoadingUserData,
        guestToken,
        fetchUser,
        clearAuthState,
        initGuestToken,
        clearGuestToken,
    };
});
