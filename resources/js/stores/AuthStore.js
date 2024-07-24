import { ref } from "vue";
import { defineStore } from "pinia";
import { logout } from "../service/auth";

export const useAuthStore = defineStore('Auth', () => {
    const userData = ref(null);
    const isAuth = ref(null);
    const user = ref(null);

    const auth = async () => {
        /* 
            Auth States:

            1: Never logged in -> No token -> state is null
            2: Logged in previously -> Token -> Token is invalid (ajax) -> State is false
            3: Logged in previously -> Token -> Token is valid (ajax) -> State is true
            4: State exists -> State is State (Logged in or out has already been checked)
        
        */
        if(!localStorage.getItem('auth-token')){
            // console.log('no auth token');
            return false;
        }
        
        if(userData.value === null && !localStorage.getItem('auth-token')){
            // console.log('never logged in');
            return false;
        }

        if(isAuth.value) return true; //checked

        try {
            const localToken = localStorage.getItem('auth-token');
            const response = await fetch(`/api/auth`, {
                method: 'get',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': "Bearer " + localToken,
                }
            })

            if(response.status !== 200){
                logout();
                return false;
            }

            const json = await response.json();

            isAuth.value = true;
            // console.log(json);
            userData.value = json.data.user;
            return true;
        } catch (error) {
            console.log(error);
            logout();
            return false;
        }
    }

    return {
        user, userData, auth, 
    };
});