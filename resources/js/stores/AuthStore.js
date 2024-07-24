import { ref } from "vue";
import { defineStore } from "pinia";
import { logout } from "../service/auth";

export const useAuthStore = defineStore('Auth', () => {
    const csrfToken = ref(document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '');

    const userData = ref(null);
    const isAuth = ref(null);

    const user = ref(null);

    const pageTitle = ref('');
    const darkMode = ref(true);

    const folders = ref([]);
    const videos = ref([]);
    const records = ref(['hah']);

    const stateDirectory = ref({id:7, name:'anime', folders: []})
    const stateFolder = ref({id:7, name:'ODDTAXI', videos: []})

    const selectedSideBar = ref('');

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

    function toggleDarkMode(init = false){
        let rootHTML = document.querySelector('html');
        let lightModeToggle = document.getElementById('dark-mode-toggle') ?? {checked: true};
        if(!init || (init && darkMode.value === false)) darkMode.value = !lightModeToggle.checked;

        darkMode.value ? rootHTML.classList.add("dark", "tw-dark") : rootHTML.classList.remove("dark", "tw-dark"); 
    };

    function cycleSideBar(target = ''){
        if(selectedSideBar.value === target) {
            selectedSideBar.value = '';
            document.getElementById('root').scrollIntoView({behavior: "smooth"});
        }
        else {
            selectedSideBar.value = target;
            document.querySelector('#list-card').scrollIntoView({behavior: "smooth"});
        }
    };

    return {
        user, userData, auth, csrfToken, 
        toggleDarkMode, cycleSideBar,
        pageTitle, darkMode, selectedSideBar,
        folders, videos, records, 
        stateDirectory, stateFolder
    };
});