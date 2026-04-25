<script setup lang="ts">
import { breakpointsTailwind, useBreakpoints } from '@vueuse/core';
import { useDropdownMenuItems } from '@/components/panels/DropdownMenuItems';
import { RouterLink, useRoute } from 'vue-router';
import { NavButton, NavLink } from '@/components/cedar-ui/button-nav';
import { getScreenSizeRank } from '@/service/util';
import { DropdownMenu } from '@/components/cedar-ui/dropdown-menu';
import { useAuthStore } from '@/stores/AuthStore';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { ref, watch } from 'vue';
import { drawer } from '@aminnausin/cedar-ui';

import DashboardSidebarDrawer from '@/components/drawers/DashboardSidebarDrawer.vue';
import SettingsSidebarDrawer from '@/components/drawers/SettingsSidebarDrawer.vue';
import VideoSidebarDrawer from '@/components/drawers/VideoSidebarDrawer.vue';
import ToggleLightMode from '@/components/inputs/ToggleLightMode.vue';
import LazyImage from '@/components/lazy/LazyImage.vue';

import MaterialSymbolsLightHistory from '~icons/material-symbols-light/history';
import CircumFolderOn from '~icons/circum/folder-on';
import CircumInboxIn from '~icons/circum/inbox-in';
import CircumMonitor from '~icons/circum/monitor';
import ProiconsMenu from '~icons/proicons/menu';

const { userData, isLoadingUserData } = storeToRefs(useAuthStore());
const { pageTitle, selectedSideBar } = storeToRefs(useAppStore());
const { dropdownItems, dropdownItemsAuth } = useDropdownMenuItems();
const { cycleSideBar } = useAppStore();

const breakpoints = useBreakpoints(breakpointsTailwind);
const route = useRoute();

const showDropdown = ref(false);

const toggleDropdown = () => {
    showDropdown.value = !showDropdown.value;
};

const toggleVideoSidebar = (sidebar: 'folders' | 'history') => {
    cycleSideBar(sidebar, 'list-card');
    if (selectedSideBar.value !== sidebar) return;

    if (getScreenSizeRank() < 3) {
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

    const SidebarComponent = sidebar === 'dashboard' ? DashboardSidebarDrawer : SettingsSidebarDrawer;
    if (getScreenSizeRank() < 3) {
        drawer.open(SidebarComponent, {
            showHeader: false,
            showFooter: false,
            onClose: () => {
                cycleSideBar(sidebar, 'left-card');
            },
        });
    }
};

const isDesktop = breakpoints.greaterOrEqual('lg');

watch(isDesktop, (now) => {
    const currentSidebar = selectedSideBar.value;

    drawer.close('programmatic');

    if (!now) {
        cycleSideBar();
        return;
    }

    switch (route.name) {
        case 'home':
            toggleVideoSidebar(currentSidebar === 'history' ? 'history' : 'folders');
            break;
        case 'settings':
        case 'preferences':
            toggleLeftSidebar('settings');
            break;
        case 'dashboard':
            toggleLeftSidebar(route.name);
            break;
        default:
            cycleSideBar();
            break;
    }
});
</script>

<template>
    <nav id="page-navbar" class="z-20 flex flex-wrap justify-between gap-2 py-1">
        <RouterLink to="/" title="Return to home library" class="group my-auto flex shrink-0 items-center rounded-md">
            <img src="/logo.svg" alt="Logo" class="ease size-4 transition-transform duration-200 group-hover:scale-120 sm:size-6" />
        </RouterLink>

        <div class="flex w-full flex-1 truncate">
            <h1 id="folder-title" class="truncate rounded-md px-0.5 text-2xl capitalize ring-inset focus-within:ring-2">
                <RouterLink v-if="$route.name === 'home'" to="/" title="Return to folder home page" class="hover:text-primary dark:hover:text-primary-muted focus:outline-none">
                    {{ pageTitle }}
                </RouterLink>
                <template v-else>{{ pageTitle }}</template>
            </h1>
        </div>

        <div id="user-options" class="group relative inline-block shrink-0" data-dropdown-toggle="user-dropdown">
            <DropdownMenu :dropdownOpen="showDropdown" @toggleDropdown="showDropdown = false" :drop-down-items="userData?.id ? dropdownItemsAuth : dropdownItems" class="mt-12">
                <template #trigger>
                    <button
                        id="user-header"
                        class="hover:text-primary dark:hover:text-primary-muted flex h-8 cursor-pointer items-center justify-center gap-2 rounded-md text-2xl capitalize"
                        @click="toggleDropdown"
                        aria-haspopup="menu"
                        :aria-expanded="showDropdown ? 'true' : 'false'"
                        aria-controls="user-dropdown"
                        title="Toggle navigation menu"
                    >
                        <h2 id="user-name" class="hidden truncate sm:block" :class="[{ 'suspense-rounded bg-surface-2 h-5 w-32': isLoadingUserData }]">
                            {{ isLoadingUserData ? '' : userData?.name || 'Guest' }}
                        </h2>

                        <LazyImage
                            :wrapper-class="'relative h-fit w-fit'"
                            class="ring-primary-active aspect-square size-7 rounded-full object-cover shadow-sm ring"
                            :src="userData?.avatar ?? '/storage/avatars/default.jpg'"
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
                    title="Toggle folder browser"
                    class="p-0"
                >
                    <CircumFolderOn height="24" width="24" />
                </NavButton>
                <NavButton
                    v-if="userData && $route.name === 'home'"
                    @click="toggleVideoSidebar('history')"
                    :label="'history'"
                    :active="selectedSideBar === 'history'"
                    title="Toggle recent watch history"
                    class="p-0"
                >
                    <MaterialSymbolsLightHistory height="24" width="24" />
                </NavButton>
                <NavButton
                    v-if="$route.name === 'dashboard'"
                    @click="toggleLeftSidebar('dashboard')"
                    :label="'dashboard'"
                    :active="selectedSideBar === 'dashboard'"
                    title="Toggle dashboard menu"
                    class="p-0"
                >
                    <ProiconsMenu height="20" width="20" />
                </NavButton>
                <NavButton
                    v-if="$route.name === 'settings' || $route.name === 'preferences'"
                    @click="toggleLeftSidebar('settings')"
                    :label="'settings'"
                    :active="selectedSideBar === 'settings'"
                    title="Toggle settings menu"
                    class="p-0"
                >
                    <ProiconsMenu height="20" width="20" />
                </NavButton>
                <NavLink v-if="$route.name != 'home'" label="home" to="/" title="Return to home library" class="p-0">
                    <CircumMonitor height="24" width="24" />
                </NavLink>
            </span>
            <ToggleLightMode class="dark:hover:border-primary w-17 border-gray-900/5 shadow-sm" />
        </div>
        <hr class="text-hr block w-full shrink-0" />
    </nav>
</template>
