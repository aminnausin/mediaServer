<script setup>
import { ref, onMounted, watch } from 'vue';
import { useAuthStore } from '@/stores/AuthStore';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';

import ToggleLightMode from '@/components/inputs/ToggleLightMode.vue';
import DropdownMenu from '@/components/pinesUI/DropdownMenu.vue';
import NavButton from '@/components/inputs/NavButton.vue';
import NavLink from '@/components/inputs/NavLink.vue';

import MaterialSymbolsLightHistory from '~icons/material-symbols-light/history';
import MaterialSymbolsLightMenu from '~icons/material-symbols-light/menu?width=24px&height=24px';
import CircumFolderOn from '~icons/circum/folder-on';
import CircumInboxIn from '~icons/circum/inbox-in';
import CircumMonitor from '~icons/circum/monitor';
import ProiconsMenu from '~icons/proicons/menu?width=24px&height=24px';

const authStore = useAuthStore();
const appStore = useAppStore();
const showDropdown = ref(false);
const username = ref('');

const { pageTitle, selectedSideBar } = storeToRefs(appStore);
const { cycleSideBar } = appStore;
const { userData } = storeToRefs(authStore);
const { auth } = authStore;

const toggleDropdown = () => {
    showDropdown.value = !showDropdown.value;
};

const handleAuthEvent = (newUserData) => {
    username.value = newUserData?.name ?? '';
};

onMounted(async () => {
    if (await auth()) handleAuthEvent(userData.value);
});

watch(userData, handleAuthEvent, { immediate: false });
</script>

<template>
    <nav id="navbar">
        <div class="flex p-1 gap-y-4 gap-2 flex-wrap justify-between">
            <span class="flex items-end sm:items-center gap-2 justify-between w-full flex-1 min-w-fit">
                <h1 id="title" class="text-2xl truncate capitalize">{{ pageTitle }}</h1>
                <section
                    id="user-options"
                    class="group inline-block relative shrink-0"
                    data-dropdown-toggle="user-dropdown"
                    aria-haspopup="true"
                >
                    <DropdownMenu :dropdownOpen="showDropdown" @toggleDropdown="showDropdown = false">
                        <template #trigger
                            ><button
                                id="user-header"
                                class="flex gap-2 text-2xl text-slate-900 dark:text-white hover:text-violet-600 items-center justify-center capitalize h-8"
                                @click="toggleDropdown"
                            >
                                <span id="user-name" class="hidden sm:block truncate" v-if="username">{{ username }}</span>
                                <span id="user-name-unauth" v-else class="text-right hidden sm:block">Guest</span>

                                <img
                                    :src="userData?.value?.avatar ?? '/storage/avatars/12345.jpg'"
                                    class="h-7 w-7 rounded-full ring-2 ring-violet-600/80 object-cover aspect-square"
                                    alt="profile picture"
                                /></button
                        ></template>
                    </DropdownMenu>
                </section>
            </span>
            <span class="flex flex-wrap sm:flex-nowrap sm:max-w-sm items-center gap-1 sm:shrink-0 justify-end sm:justify-normal sm:w-auto">
                <section id="navbar-video" class="flex items-center gap-1 text-slate-900 antialiased">
                    <NavLink v-if="$route.name != 'home'" :label="'home'" :URL="'/'" :class="`ring-1 ring-gray-900/5`">
                        <template #icon>
                            <CircumMonitor height="24" width="24" />
                        </template>
                    </NavLink>
                    <NavButton v-if="username" @click="cycleSideBar('notifications')" :label="'notifications'" class="hidden">
                        <template #icon>
                            <CircumInboxIn height="24" width="24" />
                        </template>
                    </NavButton>
                    <NavButton
                        v-if="$route.name === 'home'"
                        @click="cycleSideBar('folders', '#list-card')"
                        :label="'folders'"
                        :active="selectedSideBar === 'folders'"
                        :class="`ring-1 ring-gray-900/5`"
                    >
                        <template #icon>
                            <CircumFolderOn height="24" width="24" />
                        </template>
                    </NavButton>
                    <NavButton
                        v-if="username && $route.name === 'home'"
                        @click="cycleSideBar('history', '#list-card')"
                        :label="'history'"
                        :active="selectedSideBar === 'history'"
                        :class="`ring-1 ring-gray-900/5`"
                    >
                        <template #icon>
                            <MaterialSymbolsLightHistory height="24" width="24" />
                        </template>
                    </NavButton>
                    <NavButton
                        v-if="username && $route.name === 'dashboard'"
                        @click="cycleSideBar('dashboard', '#left-card')"
                        :label="'dashboard'"
                        :active="selectedSideBar === 'dashboard'"
                        :class="`ring-1 ring-gray-900/5`"
                    >
                        <template #icon>
                            <MaterialSymbolsLightMenu height="26" width="26" />
                        </template>
                    </NavButton>
                </section>
                <ToggleLightMode />
            </span>
        </div>
        <hr class="mt-2" />
    </nav>
</template>
