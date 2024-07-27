import { ref } from "vue";
import { defineStore } from "pinia";
import { authenticate } from "../service/authAPI";

export const useAuthStore = defineStore('Auth', () => {
    const userData = ref(null);
    const user = ref(null);

    const auth = async () => {
        /* 
            Auth States:

            1: Never logged in -> No token -> state is null
            2: Logged in previously -> Token -> Token is invalid (ajax) -> State is false
            3: Logged in previously -> Token -> Token is valid (ajax) -> State is true
            4: State exists -> State is State (Logged in or out has already been checked)
        
        */

        if(!localStorage.getItem('auth-token')) return false; // console.log('no auth token');
        
        if(userData.value === null && !localStorage.getItem('auth-token')) return false; // console.log('never logged in');

        try {
            const localToken = localStorage.getItem('auth-token');
            const { data, error, status } = await authenticate(localToken)
            
            if(error || status !== 200){ // Auth request was denied (so local data is invalid) -> don't logout because that will be another 401 anyway
                throw error ?? 'Unauthenticated';
            }

            userData.value = data.data.user;
            return true;
        } catch (error) {
            console.log(error);
            // eslint-disable-next-line no-undef
            toastr.error('Session Expired, Unable to Log In');
            clearAuthState();
            return false;
        }
    }
    
    const clearAuthState = () => {
        userData.value = null;
        localStorage.removeItem('auth-token');
    }

    return {
        user, userData, auth, clearAuthState, 
    };
});