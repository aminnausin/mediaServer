import { ref } from "vue";
import { defineStore } from "pinia";

export const useAppStore = defineStore('App', () => {
    const pageTitle = ref('');
    const lightMode = ref(null);
    const selectedSideBar = ref('');

    function toggleDarkMode(){
        let rootHTML = document.querySelector('html');

        localStorage.setItem('lightMode', lightMode.value);
        lightMode.value ? rootHTML.classList.remove("dark", "tw-dark") : rootHTML.classList.add("dark", "tw-dark"); 
    };

    function initDarkMode(){
        let init = lightMode.value === null;
        let cachedState = localStorage.getItem('lightMode');
        if(!init) return;

        lightMode.value = cachedState === 'true';
        localStorage.setItem('lightMode', lightMode.value);
    }

    function cycleSideBar(target = ''){
        if(selectedSideBar.value === target) {
            selectedSideBar.value = '';
            document.getElementById('root').scrollIntoView({behavior: "smooth"});
            return;
        }
        selectedSideBar.value = target;
    };

    return {
        initDarkMode, toggleDarkMode, cycleSideBar,
        pageTitle, lightMode, selectedSideBar,
    };
});