<script setup>
import LightModeToggle from '../inputs/LightModeToggle.vue';
import UserDropdown from '../user/UserDropdown.vue';
import NavButton from '../inputs/NavButton.vue';
import NavLink from '../inputs/NavLink.vue';

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
        if (!this.document.querySelector("#user_options")?.contains(e.target)) {
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
        <div class="flex p-1 gap-y-3 flex-wrap justify-between">
            <h1 id="title" class="text-2xl">{{ pageTitle }}</h1>
            <span class="flex flex-wrap sm:flex-nowrap sm:max-w-sm items-center gap-2 sm:shrink-0">
                <section id="user_options" class="group inline-block relative" data-dropdown-toggle="user_dropdown"
                    aria-haspopup="true">
                    <button id="user_header"
                        class="flex gap-2 text-2xl text-slate-900 dark:text-white hover:text-accent-600 dark:hover:text-accent-600 items-center justify-center"
                        @click="toggleDropdown">

                        <span id="user_name" class="hidden sm:block" v-if="username">{{ username }}</span>
                        <span id="user_name_unauth" v-else class="text-right hidden sm:block">Guest</span>

                        <img :src="userData?.value?.avatar ?? '/storage/avatars/12345.jpg'"
                            class="h-7 w-7 rounded-full ring-2 ring-accent-600/60 object-cover aspect-square"
                            alt="profile picture">
                    </button>
                    <UserDropdown v-if="showDropdown" />
                </section>

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
                    <NavLink v-if="$route.name === 'history'" :label="'home'" :URL="'/'">
                        <template #icon>
                            <CircumMonitor height="24" width="24" />
                        </template>
                    </NavLink>
                </section>

                <LightModeToggle />
            </span>
        </div>
        <hr class="mt-2" />
    </nav>
</template>