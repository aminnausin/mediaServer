<script setup lang="ts">
import { useDropdownMenuItems } from '@/components/panels/DropdownMenuItems';
import { NavButton, NavLink } from '@/components/cedar-ui/button-nav';
import { getScreenSize } from '@/service/util';
import { DropdownMenu } from '@/components/cedar-ui/dropdown-menu';
import { useAuthStore } from '@/stores/AuthStore';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { drawer } from '@aminnausin/cedar-ui';
import { ref } from 'vue';

import DashboardSidebarDrawer from '@/components/drawers/DashboardSidebarDrawer.vue';
import SettingsSidebarDrawer from '@/components/drawers/SettingsSidebarDrawer.vue';
import VideoSidebarDrawer from '@/components/drawers/VideoSidebarDrawer.vue';
import ToggleLightMode from '@/components/inputs/ToggleLightMode.vue';

import MaterialSymbolsLightHistory from '~icons/material-symbols-light/history';
import CircumFolderOn from '~icons/circum/folder-on';
import CircumInboxIn from '~icons/circum/inbox-in';
import CircumMonitor from '~icons/circum/monitor';
import ProiconsMenu from '~icons/proicons/menu';

const showDropdown = ref(false);

const { userData, isLoadingUserData } = storeToRefs(useAuthStore());
const { pageTitle, selectedSideBar } = storeToRefs(useAppStore());
const { dropdownItems, dropdownItemsAuth } = useDropdownMenuItems();
const { cycleSideBar } = useAppStore();

const toggleDropdown = () => {
    showDropdown.value = !showDropdown.value;
};

const toggleVideoSidebar = (sidebar: 'folders' | 'history') => {
    cycleSideBar(sidebar, 'list-card');
    if (selectedSideBar.value !== sidebar) return;

    const screenSize = getScreenSize();
    if (screenSize === 'default' || screenSize === 'sm') {
        drawer.open(VideoSidebarDrawer, {
            showHeader: false,
            showFooter: false,
            onClose: () => {
                cycleSideBar(sidebar, 'list-card');
            },
        });
    }
};

// Hardcoded could be much better elsewhere
const toggleLeftSidebar = (sidebar: 'dashboard' | 'settings') => {
    cycleSideBar(sidebar, 'left-card', false);

    if (selectedSideBar.value !== sidebar) return;

    const screenSize = getScreenSize();
    const SidebarComponent = sidebar === 'dashboard' ? DashboardSidebarDrawer : SettingsSidebarDrawer;
    if (screenSize === 'default' || screenSize === 'sm') {
        drawer.open(SidebarComponent, {
            showHeader: false,
            showFooter: false,
            onClose: () => {
                cycleSideBar(sidebar, 'left-card');
            },
        });
    }
};
</script>

<template>
    <nav id="page-navbar" class="z-20 flex flex-wrap justify-between gap-2 py-1">
        <h1 id="page-title" class="w-full flex-1 truncate text-2xl capitalize" :title="pageTitle">{{ pageTitle }}</h1>
        <div id="user-options" class="group relative inline-block shrink-0" data-dropdown-toggle="user-dropdown">
            <DropdownMenu :dropdownOpen="showDropdown" @toggleDropdown="showDropdown = false" :drop-down-items="userData?.id ? dropdownItemsAuth : dropdownItems" class="mt-12">
                <template #trigger>
                    <button
                        id="user-header"
                        class="hover:text-primary dark:hover:text-primary-muted flex h-8 cursor-pointer items-center justify-center gap-2 text-2xl capitalize"
                        @click="toggleDropdown"
                        aria-haspopup="menu"
                        :aria-expanded="showDropdown ? 'true' : 'false'"
                        aria-controls="user-dropdown"
                        title="Open Dropdown Menu"
                    >
                        <h2 id="user-name" class="hidden truncate sm:block" :class="[{ 'suspense-rounded bg-surface-2 h-5 w-32': true }]">
                            {{ true ? '' : userData?.name || 'Guest' }}
                        </h2>

                        <img
                            :src="userData?.avatar ?? '/storage/avatars/default.jpg'"
                            class="ring-primary-active aspect-square h-7 w-7 rounded-full object-cover ring"
                            alt="profile"
                        />
                    </button>
                </template>
            </DropdownMenu>
        </div>

        <div class="ml-auto flex flex-wrap items-center justify-end gap-1 sm:w-auto sm:max-w-sm sm:shrink-0 sm:flex-nowrap sm:justify-normal">
            <span id="video-navbar" class="flex items-center gap-1 antialiased">
                <NavButton v-if="userData" @click="cycleSideBar('notifications')" :label="'notifications'" class="hidden" active>
                    <CircumInboxIn height="24" width="24" />
                </NavButton>
                <NavButton
                    v-if="$route.name === 'home'"
                    @click="toggleVideoSidebar('folders')"
                    :label="'folders'"
                    :active="selectedSideBar == 'folders'"
                    title="Toggle Folder List"
                    class="p-0"
                >
                    <CircumFolderOn height="24" width="24" />
                </NavButton>
                <NavButton
                    v-if="userData && $route.name === 'home'"
                    @click="toggleVideoSidebar('history')"
                    :label="'history'"
                    :active="selectedSideBar === 'history'"
                    title="Toggle Watch History List"
                    class="p-0"
                >
                    <MaterialSymbolsLightHistory height="24" width="24" />
                </NavButton>
                <NavButton
                    v-if="$route.name === 'dashboard'"
                    @click="toggleLeftSidebar('dashboard')"
                    :label="'dashboard'"
                    :active="selectedSideBar === 'dashboard'"
                    title="Toggle Dashboard Menu"
                    class="p-0"
                >
                    <ProiconsMenu height="20" width="20" />
                </NavButton>
                <NavButton
                    v-if="$route.name === 'settings' || $route.name === 'preferences'"
                    @click="toggleLeftSidebar('settings')"
                    :label="'settings'"
                    :active="selectedSideBar === 'settings'"
                    title="Toggle Settings Menu"
                    class="p-0"
                >
                    <ProiconsMenu height="20" width="20" />
                </NavButton>
                <NavLink v-if="$route.name != 'home'" label="home" to="/" title="Return to Home Library" class="p-0">
                    <CircumMonitor height="24" width="24" />
                </NavLink>
            </span>
            <ToggleLightMode class="dark:hover:border-primary w-[68px] border border-gray-900/5 shadow-lg" />
        </div>
        <hr class="block w-full shrink-0" />
    </nav>
</template>
