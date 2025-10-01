<script setup lang="ts">
import { useAuthStore } from '@/stores/AuthStore';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { ref } from 'vue';

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

const toggleDropdown = () => {
    showDropdown.value = !showDropdown.value;
};
</script>

<template>
    <nav id="page-navbar" class="z-20 flex flex-wrap justify-between gap-2 py-1">
        <span class="flex w-full flex-1 items-end justify-between gap-2 sm:items-center">
            <h1 id="page-title" class="truncate text-2xl capitalize">{{ pageTitle }}</h1>
            <section id="user-options" class="group relative inline-block shrink-0" data-dropdown-toggle="user-dropdown">
                <DropdownMenu :dropdownOpen="showDropdown" @toggleDropdown="showDropdown = false">
                    <template #trigger
                        ><button
                            id="user-header"
                            class="flex h-8 items-center justify-center gap-2 text-2xl capitalize hover:text-violet-600 dark:hover:text-violet-500"
                            @click="toggleDropdown"
                            aria-haspopup="menu"
                            :aria-expanded="showDropdown ? 'true' : 'false'"
                            aria-controls="user-dropdown"
                        >
                            <h2
                                id="user-name"
                                class="hidden truncate sm:block"
                                :class="[{ 'my-auto h-5 w-32 animate-pulse rounded-full bg-neutral-200 dark:bg-neutral-800': isLoadingUserData }]"
                            >
                                {{ isLoadingUserData ? '' : userData?.name || 'Guest' }}
                            </h2>

                            <img
                                :src="userData?.avatar ?? '/storage/avatars/default.jpg'"
                                class="aspect-square h-7 w-7 rounded-full object-cover ring-2 ring-violet-700"
                                alt="profile"
                            /></button
                    ></template>
                </DropdownMenu>
            </section>
        </span>
        <span class="ml-auto flex flex-wrap items-center justify-end gap-1 sm:w-auto sm:max-w-sm sm:shrink-0 sm:flex-nowrap sm:justify-normal">
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
