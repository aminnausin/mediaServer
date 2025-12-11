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
        <h1 id="page-title" class="w-full flex-1 truncate text-2xl capitalize" :title="pageTitle">{{ pageTitle }}</h1>
        <section id="user-options" class="group relative inline-block shrink-0" data-dropdown-toggle="user-dropdown">
            <DropdownMenu :dropdownOpen="showDropdown" @toggleDropdown="showDropdown = false">
                <template #trigger>
                    <button
                        id="user-header"
                        class="flex h-8 cursor-pointer items-center justify-center gap-2 text-2xl capitalize hover:text-violet-600 dark:hover:text-violet-500"
                        @click="toggleDropdown"
                        aria-haspopup="menu"
                        :aria-expanded="showDropdown ? 'true' : 'false'"
                        aria-controls="user-dropdown"
                        title="Open Dropdown Menu"
                    >
                        <h2
                            id="user-name"
                            class="hidden truncate sm:block"
                            :class="[{ 'my-auto h-5 w-32 animate-pulse rounded-full bg-neutral-200 dark:bg-neutral-800': isLoadingUserData }]"
                        >
                            {{ isLoadingUserData ? '' : userData?.name || 'Guest' }}
                        </h2>

                        <img :src="userData?.avatar ?? '/storage/avatars/default.jpg'" class="aspect-square h-7 w-7 rounded-full object-cover ring ring-violet-700" alt="profile" />
                    </button>
                </template>
            </DropdownMenu>
        </section>

        <section class="ml-auto flex flex-wrap items-center justify-end gap-1 sm:w-auto sm:max-w-sm sm:shrink-0 sm:flex-nowrap sm:justify-normal">
            <span id="video-navbar" class="flex items-center gap-1 antialiased">
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
                    title="Toggle Folder List"
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
                    title="Toggle Watch History List"
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
                    title="Toggle Dashboard Menu"
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
                    title="Toggle Settings Menu"
                >
                    <template #icon>
                        <ProiconsMenu height="20" width="20" />
                    </template>
                </NavButton>
                <NavLink v-if="$route.name != 'home'" :label="'home'" :URL="'/'" :class="`ring-1 ring-gray-900/5`" title="Return to Home Library">
                    <template #icon>
                        <CircumMonitor height="24" width="24" />
                    </template>
                </NavLink>
            </span>
            <ToggleLightMode class="w-[68px] border border-gray-900/5 shadow-lg dark:hover:border-violet-600" />
        </section>
        <hr class="block w-full shrink-0" />
    </nav>
</template>
