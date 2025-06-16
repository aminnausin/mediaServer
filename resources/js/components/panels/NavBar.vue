<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/AuthStore';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';

import ToggleLightMode from '@/components/inputs/ToggleLightMode.vue';
import DropdownMenu from '@/components/pinesUI/DropdownMenu.vue';
import NavButton from '@/components/inputs/NavButton.vue';
import NavLink from '@/components/inputs/NavLink.vue';

import MaterialSymbolsLightHistory from '~icons/material-symbols-light/history';
import CircumFolderOn from '~icons/circum/folder-on';
import CircumInboxIn from '~icons/circum/inbox-in';
import CircumMonitor from '~icons/circum/monitor';
import ProiconsMenu from '~icons/proicons/menu';

const showDropdown = ref(false);

const { userData, isLoadingUserData } = storeToRefs(useAuthStore());
const { pageTitle, selectedSideBar } = storeToRefs(useAppStore());
const { cycleSideBar } = useAppStore();
const { auth } = useAuthStore();

const toggleDropdown = () => {
    showDropdown.value = !showDropdown.value;
};

onMounted(async () => {
    await auth();
});
</script>

<template>
    <nav id="page-navbar" class="flex py-1 gap-2 flex-wrap justify-between z-20">
        <span class="flex items-end sm:items-center gap-2 justify-between w-full flex-1">
            <h1 id="page-title" class="text-2xl truncate capitalize">{{ pageTitle }}</h1>
            <section id="user-options" class="group inline-block relative shrink-0" data-dropdown-toggle="user-dropdown">
                <DropdownMenu :dropdownOpen="showDropdown" @toggleDropdown="showDropdown = false">
                    <template #trigger
                        ><button
                            id="user-header"
                            class="flex gap-2 text-2xl hover:text-violet-600 items-center justify-center capitalize h-8"
                            @click="toggleDropdown"
                            aria-haspopup="menu"
                            :aria-expanded="showDropdown"
                            aria-controls="user-dropdown"
                        >
                            <h2
                                id="user-name"
                                class="hidden sm:block truncate"
                                :class="[{ 'bg-neutral-200 dark:bg-neutral-800 rounded-full w-32 h-5 my-auto animate-pulse': isLoadingUserData }]"
                            >
                                {{ isLoadingUserData ? '' : userData?.name || 'Guest' }}
                            </h2>

                            <img
                                :src="userData?.avatar ?? '/storage/avatars/default.jpg'"
                                class="h-7 w-7 rounded-full ring-2 ring-violet-700 object-cover aspect-square"
                                alt="profile"
                            /></button
                    ></template>
                </DropdownMenu>
            </section>
        </span>
        <span class="flex flex-wrap sm:flex-nowrap sm:max-w-sm items-center gap-1 sm:shrink-0 justify-end sm:justify-normal sm:w-auto ml-auto">
            <section id="video-navbar" class="flex items-center gap-1 antialiased">
                <NavButton v-if="userData" @click="cycleSideBar('notifications')" :label="'notifications'" class="hidden">
                    <template #icon>
                        <CircumInboxIn height="24" width="24" />
                    </template>
                </NavButton>
                <NavButton
                    v-if="$route.name === 'home'"
                    @click="cycleSideBar('folders', 'list-card')"
                    :label="'folders'"
                    :active="selectedSideBar === 'folders'"
                    :class="`ring-1 ring-gray-900/5`"
                >
                    <template #icon>
                        <CircumFolderOn height="24" width="24" />
                    </template>
                </NavButton>
                <NavButton
                    v-if="userData && $route.name === 'home'"
                    @click="cycleSideBar('history', 'list-card')"
                    :label="'history'"
                    :active="selectedSideBar === 'history'"
                    :class="`ring-1 ring-gray-900/5`"
                >
                    <template #icon>
                        <MaterialSymbolsLightHistory height="24" width="24" />
                    </template>
                </NavButton>
                <NavButton
                    v-if="$route.name === 'dashboard'"
                    @click="cycleSideBar('dashboard', 'left-card')"
                    :label="'dashboard'"
                    :active="selectedSideBar === 'dashboard'"
                    :class="`ring-1 ring-gray-900/5`"
                >
                    <template #icon>
                        <ProiconsMenu height="20" width="20" />
                    </template>
                </NavButton>
                <NavButton
                    v-if="$route.name === 'settings' || $route.name === 'preferences'"
                    @click="cycleSideBar('settings', 'left-card')"
                    :label="'settings'"
                    :active="selectedSideBar === 'settings'"
                    :class="`ring-1 ring-gray-900/5`"
                >
                    <template #icon>
                        <ProiconsMenu height="20" width="20" />
                    </template>
                </NavButton>
                <NavLink v-if="$route.name != 'home'" :label="'home'" :URL="'/'" :class="`ring-1 ring-gray-900/5`">
                    <template #icon>
                        <CircumMonitor height="24" width="24" />
                    </template>
                </NavLink>
            </section>
            <ToggleLightMode />
        </span>
        <hr class="block w-full shrink-0" />
    </nav>
</template>
