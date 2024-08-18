import { ref } from "vue";
import { defineStore } from "pinia";

export const useAppStore = defineStore("App", () => {
    const pageTitle = ref("");
    const lightMode = ref(null);
    const ambientMode = ref(null);
    const selectedSideBar = ref("");
    const scrollLock = ref(false);

    function toggleDarkMode() {
        let rootHTML = document.querySelector("html");

        localStorage.setItem("lightMode", lightMode.value);
        lightMode.value
            ? rootHTML.classList.remove("dark", "tw-dark")
            : rootHTML.classList.add("dark", "tw-dark");
    }

    function initDarkMode() {
        let init = lightMode.value === null;
        let cachedState = localStorage.getItem("lightMode");
        if (!init) return;

        lightMode.value = cachedState === "true";
        localStorage.setItem("lightMode", lightMode.value);
    }

    function setAmbientMode() {
        localStorage.setItem("ambientMode", ambientMode.value);
    }

    function initAmbientMode() {
        let init = ambientMode.value === null;
        let cachedState = localStorage.getItem("ambientMode");
        if (!init) return;

        ambientMode.value = cachedState === "true";
        localStorage.setItem("ambientMode", ambientMode.value);
    }

    function cycleSideBar(target = "") {
        if (selectedSideBar.value === target) {
            selectedSideBar.value = "";
            document
                .getElementById("root")
                .scrollIntoView({ behavior: "smooth" });
            return;
        }
        selectedSideBar.value = target;
    }

    function setScrollLock(state = false){
        scrollLock.value = state;
    }

    return {
        initDarkMode, toggleDarkMode, lightMode, 
        initAmbientMode, setAmbientMode, ambientMode, 
        cycleSideBar, selectedSideBar, 
        pageTitle,
        scrollLock, setScrollLock
    };
});
