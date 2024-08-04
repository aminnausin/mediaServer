<script setup>
import ToggleLightMode from '../inputs/ToggleLightMode.vue';
import NavButton from '../inputs/NavButton.vue';
import NavLink from '../inputs/NavLink.vue';
import DropdownMenu from '../pinesUI/DropdownMenu.vue';

import CircumFolderOn from '~icons/circum/folder-on';
import MaterialSymbolsLightHistory from '~icons/material-symbols-light/history';
import CircumMonitor from '~icons/circum/monitor';
import CircumInboxIn from '~icons/circum/inbox-in';

import { ref, onMounted, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { useAuthStore } from '../../stores/AuthStore'
import { useAppStore } from '../../stores/AppStore';


const appStore = useAppStore();
const authStore = useAuthStore();
const { pageTitle, selectedSideBar } = storeToRefs(appStore);
const { userData } = storeToRefs(authStore);
const { cycleSideBar } = appStore;
const { auth } = authStore;

const showDropdown = ref(false);
const username = ref('')


const toggleDropdown = () => {
    showDropdown.value = !showDropdown.value;
}

window.addEventListener('click', function (e) {
    try {
        if (!this.document.querySelector("#user-options")?.contains(e.target)) {
            showDropdown.value = false;
        }
    } catch (error) {
        console.log(error);
    }
});

const handleAuthEvent = (newUserData) => {
    username.value = newUserData?.name ?? '';
}

onMounted(async () => {
    if (await auth()) handleAuthEvent(userData.value)
})

watch(userData, handleAuthEvent, { immediate: false });
</script>

<template>
    <nav id="navbar">
        <div class="flex p-1 gap-y-4 gap-2 flex-wrap sm:flex-nowrap justify-between">
            <span class="flex items-end sm:items-center gap-2 justify-between w-full">
                <h1 id="title" class="text-2xl line-clamp-2 sm:line-clamp-1 capitalize">{{ pageTitle }}</h1>

                <section id="user-options" class="group inline-block relative shrink-0" data-dropdown-toggle="user-dropdown"
                    aria-haspopup="true">
                    <button id="user-header"
                        class="flex gap-2 text-2xl text-slate-900 dark:text-white hover:text-violet-500 dark:hover:text-violet-500 items-center justify-center capitalize"
                        @click="toggleDropdown">

                        <span id="user-name" class="hidden sm:block" v-if="username">{{ username }}</span>
                        <span id="user-name-unauth" v-else class="text-right hidden sm:block">Guest</span>

                        <img :src="userData?.value?.avatar ?? '/storage/avatars/12345.jpg'"
                            class="h-7 w-7 rounded-full ring-2 ring-violet-600/60 object-cover aspect-square"
                            alt="profile picture">
                    </button>
                    <DropdownMenu :dropdownOpen="showDropdown" @toggleDropdown="dropdownOpen = false"/>
                </section>
            </span>
            <span class="flex flex-wrap sm:flex-nowrap sm:max-w-sm items-center gap-1 sm:shrink-0 justify-end sm:justify-normal w-full sm:w-auto">
                <section id="navbar-video" class="flex items-center gap-1 text-slate-900 antialiased">
                    <NavButton v-if="username" @click="cycleSideBar('notifications')" :label="'notifications'" class="hidden">
                        <template #icon>
                            <CircumInboxIn height="24" width="24" />
                        </template>
                    </NavButton>
                    <NavButton v-if="$route.name === 'home'" @click="cycleSideBar('folders')" :label="'folders'" :active="selectedSideBar === 'folders'">
                        <template #icon>
                            <CircumFolderOn height="24" width="24" />
                        </template>
                    </NavButton>
                    <NavButton v-if="username && $route.name === 'home'" @click="cycleSideBar('history')"
                        :label="'history'" :active="selectedSideBar === 'history'">
                        <template #icon>
                            <MaterialSymbolsLightHistory height="24" width="24" />
                        </template>
                    </NavButton>
                    <NavLink v-if="$route.name === 'history' || $route.name === 'settings' || $route.name === 'profile'" :label="'home'" :URL="'/'">
                        <template #icon>
                            <CircumMonitor height="24" width="24" />
                        </template>
                    </NavLink>
                </section>
                <ToggleLightMode />
            </span>
        </div>
        <hr class="mt-2" />
    </nav>
</template>