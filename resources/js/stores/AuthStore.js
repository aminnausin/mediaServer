import { ref, computed } from "vue";
import { defineStore } from "pinia";

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

    const login = async (credentials) => {
        // const remember_me = document.getElementById("remember_me");
        const response = {};
        fetch(`/api/login`, {
            method: 'post',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                // 'email': $('#email').val(),
                // 'password': $('#password').val(), //'123456789!aB'
                // 'remember_me': remember_me.checked,
                // '_token' : csrfToken
                ...credentials
            })
        }).then((response) => 
            response.json()
        ).then((json) => {
            console.log(json);
            response['data'] = json?.data;
        }).catch((error) => {
            console.log(error);
            response['error'] = error;
        });

        return Promise.resolve(response);
    };

    const logout = () => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
        fetch(`/api/logout`, {
            method: 'delete',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        }).then(response => 
            response.json()
        ).then(json => {
            console.log(json);
            localStorage.clear('auth-token');
            if(json.success == true) window.location.href = "/";
        }).catch((error) => {
            console.log(error);
        });
    };

    const register = () => {

    };

    const auth = async () => {
        /* 
            Auth States:

            1: Never logged in -> No token -> state is null
            2: Logged in previously -> Token -> Token is invalid (ajax) -> State is false
            3: Logged in previously -> Token -> Token is valid (ajax) -> State is true
            4: State exists -> State is State (Logged in or out has already been checked)
        
        */
        if(!localStorage.getItem('auth-token')){
            console.log('no auth token');
            return false;
        }
        
        if(userData.value === null && !localStorage.getItem('auth-token')){
            console.log('never logged in');
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
                    'X-CSRF-TOKEN': localToken
                }
            })

            const json = await response.json();
            if(json){
                isAuth.value = true;
                console.log(json);
                userData.value = json.data.user;
                return true;
            }
        } catch (error) {
            console.log(error);
            return false;
        }
    }

    function toggleDarkMode(init = false){
        let rootHTML = document.querySelector('html');
        let lightModeToggle = document.getElementById('dark-mode-toggle') ?? {checked: true};
        if(!init || (init && darkMode.value === false)) darkMode.value = !lightModeToggle.checked;

        // let currElemClass = darkMode.value ? 'light-mode' : 'dark-mode';
        // let nextElemClass = darkMode.value ? 'dark-mode' : 'light-mode';
        // let currBtnClass = darkMode.value ? 'btn-light' : 'btn-dark';
        // let nextBtnClass = darkMode.value ? 'btn-dark' : 'btn-light';

        darkMode.value ? rootHTML.classList.add("dark", "tw-dark") : rootHTML.classList.remove("dark", "tw-dark");

        // let elems = document.querySelectorAll('.' + currElemClass);
        // let btns = document.querySelectorAll('.' + currBtnClass);

        // for (let el of elems) {
            // el.classList.replace(currElemClass, nextElemClass);
        // }

        // for (let btn of btns) {
            // btn.classList.replace(currBtnClass, nextBtnClass);
        // }
        
    };

    function cycleSideBar(target = ''){
        if(selectedSideBar.value === target) {
            selectedSideBar.value = '';
            document.getElementById('root').scrollIntoView({behavior: "smooth"});
        }
        else {
            selectedSideBar.value = target;
            // document.querySelector('#list-card').scrollIntoView({behavior: "smooth"});
        }
    };

    return {
        user, userData, auth, csrfToken, 
        login, logout, register, 
        toggleDarkMode, cycleSideBar,
        pageTitle, darkMode, selectedSideBar,
        folders, videos, records, 
        stateDirectory, stateFolder
    };
});