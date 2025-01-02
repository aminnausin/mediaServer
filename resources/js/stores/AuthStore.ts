import type { UserResource } from '@/types/resources';

import { authenticate } from '@/service/authAPI';
import { defineStore } from 'pinia';
import { AxiosError } from 'axios';
import { toast } from '@/service/toaster/toastService';
import { ref } from 'vue';

export const useAuthStore = defineStore('Auth', () => {
    const userData = ref<null | UserResource>(null);

    const auth = async () => {
        /*
            Auth States:

            1: Never logged in -> No token -> state is null
            2: Logged in previously -> Token -> Token is invalid (ajax) -> State is false
            3: Logged in previously -> Token -> Token is valid (ajax) -> State is true
            4: State exists -> State is State (Logged in or out has already been checked)

        */

        if (!localStorage.getItem('auth-token')) return false; // console.log('no auth token');

        if (userData.value === null && !localStorage.getItem('auth-token')) return false; // console.log('never logged in');

        try {
            const localToken = localStorage.getItem('auth-token');
            const { data, status } = await authenticate(localToken);

            if (status !== 200) {
                // Auth request was denied (so local data is invalid) -> don't logout because that will be another 401 anyway
                throw new AxiosError('Not Authenticated', status.toString()) ?? 'Unauthenticated';
            }

            userData.value = data.data.user;

            return true;
        } catch (error) {
            console.log(error);
            toast.add('Session Expired', { type: 'warning', description: `Please log in again.` });
            clearAuthState();
            return false;
        }
    };

    const clearAuthState = () => {
        userData.value = null;
        localStorage.removeItem('auth-token');
    };

    return {
        userData,
        auth,
        clearAuthState,
    };
});
