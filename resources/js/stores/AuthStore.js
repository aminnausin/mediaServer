import { ref, computed } from "vue";
import { defineStore } from "pinia";

export const useAuthStore = defineStore('Auth', () => {
    var csrfToken = ref(document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '');

    const user = ref({
        username: 'Aminushki',
        email: 'test@tmu.ca',
        avatar:'/storage/avatars/12345.jpg'
    });

    const pageTitle = ref('Full User History')
    const auth = ref(true);
    const darkMode = ref(true);

    const folders = ref([]);
    const videos = ref([]);
    const records = ref([]);


    const login = () => {
        const remember_me = document.getElementById("remember_me");
        fetch(`/api/login`, {
            method: 'post',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                'email': $('#email').val(),
                'password': $('#password').val(), //'123456789!aB'
                'remember_me': remember_me.checked
            })
        }).then((response) => 
            response.json()
        ).then((json) => {
            console.log(json);
            if(json.success){
                localStorage.setItem('auth-token', json.data.token)
                toastr['info'](localStorage.getItem('auth-token'));
                window.location.href = '/';
            }
        }).catch((error) => {
            console.log(error);
        });
    };

    const logout = () => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
        fetch(`/logout`, {
            method: 'post',
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
    }

    const register = () => {

    }

    function toggleDarkMode(init = false){
        let rootHTML = document.querySelector('html');
        let lightModeToggle = document.getElementById('dark-mode-toggle') ?? {checked: true};
        if(!init || (init && darkMode.value === false)) darkMode.value = !lightModeToggle.checked;

        let currElemClass = darkMode.value ? 'light-mode' : 'dark-mode';
        let nextElemClass = darkMode.value ? 'dark-mode' : 'light-mode';
        let currBtnClass = darkMode.value ? 'btn-light' : 'btn-dark';
        let nextBtnClass = darkMode.value ? 'btn-dark' : 'btn-light';

        darkMode.value ? rootHTML.classList.add("dark", "tw-dark") : rootHTML.classList.remove("dark", "tw-dark");

        let elems = document.querySelectorAll('.' + currElemClass);
        let btns = document.querySelectorAll('.' + currBtnClass);

        for (let el of elems) {
            el.classList.replace(currElemClass, nextElemClass);
        }

        for (let btn of btns) {
            btn.classList.replace(currBtnClass, nextBtnClass);
        }
        
    }

    return {user, auth, login, logout, register, pageTitle, csrfToken, toggleDarkMode, darkMode, folders, videos, records};
});