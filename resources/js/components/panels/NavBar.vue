<script setup lang="ts">
import type { Component } from 'vue';

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

import FolderDetailsSidebarDrawer from '@/components/drawers/FolderDetailsSidebarDrawer.vue';
import VideoSidebarDrawer from '@/components/drawers/VideoSidebarDrawer.vue';
import ToggleLightMode from '@/components/inputs/ToggleLightMode.vue';
import SidebarDrawer from '@/components/drawers/SidebarDrawer.vue';
import LazyImage from '@/components/lazy/LazyImage.vue';

import MaterialSymbolsLightHistory from '~icons/material-symbols-light/history';
import ProiconsSparkle2 from '~icons/proicons/sparkle-2';
import CircumMonitor from '~icons/circum/monitor';
import ProiconsMenu from '~icons/proicons/menu';
import IconFolder from '@/components/icons/IconFolder.vue';

const { dropdownItems, dropdownItemsAuth } = useDropdownMenuItems();
const { userData, isLoadingUserData } = storeToRefs(useAuthStore());
const { pageTitle, selectedSideBar } = storeToRefs(useAppStore());
const { cycleSideBar } = useAppStore();

const breakpoints = useBreakpoints(breakpointsTailwind);
const route = useRoute();

const showDropdown = ref(false);

const toggleDropdown = () => {
    showDropdown.value = !showDropdown.value;
};

const toggleRightSidebar = (sidebar: 'feed' | 'folders' | 'history', drawerComponent: Component = SidebarDrawer) => {
    cycleSideBar(sidebar, 'list-card');

    if (selectedSideBar.value !== sidebar) return;

    if (getScreenSizeRank() < 3 && drawerComponent) {
        drawer.open(drawerComponent, {
            showHeader: false,
            showFooter: false,
            onClose: () => {
                cycleSideBar(sidebar, 'list-card');
            },
        });
    }
};

// Hardcoded could be much better elsewhere
const toggleLeftSidebar = (sidebar: 'dashboard' | 'settings' | 'config') => {
    cycleSideBar(sidebar, 'left-card', false);

    if (selectedSideBar.value !== sidebar) return;

    if (getScreenSizeRank() < 3) {
        drawer.open(SidebarDrawer, {
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
            toggleRightSidebar(currentSidebar === 'history' ? 'history' : 'folders', VideoSidebarDrawer);
            break;
        case 'explore':
            toggleRightSidebar('feed');
            break;
        case 'folder':
            toggleRightSidebar('folders', FolderDetailsSidebarDrawer);
            break;
        case 'config':
            toggleLeftSidebar('config');
            break;
        case 'settings':
        case 'preferences':
            toggleLeftSidebar('settings');
            break;
        case 'dashboard':
            toggleLeftSidebar('dashboard');
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
            <img src="/logo.svg" alt="Logo" class="ease size-5 transition-transform duration-200 group-hover:scale-120 sm:size-5.5" />
        </RouterLink>

        <div class="flex w-full flex-1 truncate">
            <h1 id="folder-title" class="truncate rounded-md px-0.5 text-2xl capitalize ring-inset focus-within:ring-2">
                <RouterLink
                    v-if="$route.name === 'home'"
                    :to="$route.path"
                    title="Return to folder home page"
                    class="hover:text-primary dark:hover:text-primary-muted focus:outline-none"
                >
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
                            :wrapper-class="'size-fit'"
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
                <template v-if="$route.name === 'home' || $route.name === 'folder-details'">
                    <NavButton
                        @click="toggleRightSidebar('folders', $route.name === 'home' ? VideoSidebarDrawer : FolderDetailsSidebarDrawer)"
                        :label="'folders'"
                        :active="selectedSideBar == 'folders'"
                        title="Toggle folder browser"
                        class="p-0"
                    >
                        <IconFolder class="size-6" stroke-width="0.0" />
                    </NavButton>
                    <NavButton
                        v-if="userData"
                        @click="toggleRightSidebar('history', VideoSidebarDrawer)"
                        :label="'history'"
                        :active="selectedSideBar === 'history'"
                        title="Toggle recent watch history"
                        class="p-0"
                    >
                        <MaterialSymbolsLightHistory class="size-6" stroke-width="0.25" stroke="currentColor" />
                    </NavButton>
                </template>
                <NavButton
                    v-if="$route.name === 'explore'"
                    @click="toggleRightSidebar('feed')"
                    :label="'activity feed'"
                    :active="selectedSideBar == 'feed'"
                    title="Toggle activity feed"
                    class="p-0"
                >
                    <ProiconsSparkle2 class="size-6" />
                    <!-- <svg class="size-6" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                        <path d="M0 0h24v24H0z" fill="none" />
                        <path
                            fill="currentColor"
                            d="M6.528 7.75a.75.75 0 1 1-1.5 0a.75.75 0 0 1 1.5 0M5.781 15a.75.75 0 0 0 0 1.5h5a.75.75 0 1 0 0-1.5zm-.75-3.25a.75.75 0 0 1 .75-.75h5a.75.75 0 1 1 0 1.5h-5a.75.75 0 0 1-.75-.75M15 21a1.986 1.986 0 0 0 1.934-1.597l.48-2.403h2.836A1.75 1.75 0 0 0 22 15.25V9.261A2.26 2.26 0 0 0 19.75 7v-.004H14.5V5.25A2.25 2.25 0 0 0 12.25 3h-8A2.25 2.25 0 0 0 2 5.25v12.5A3.25 3.25 0 0 0 5.25 21zM3.5 5.25a.75.75 0 0 1 .75-.75h8a.75.75 0 0 1 .75.75v13.764q0 .251.06.486H5.25a1.75 1.75 0 0 1-1.75-1.75zm11 3.246h3.11a2 2 0 0 0-.088.322l-2.059 10.291a.486.486 0 0 1-.963-.095zm4.493.616a.761.761 0 0 1 1.507.15v5.988a.25.25 0 0 1-.25.25h-2.535z"
                        />
                    </svg> -->
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
                <NavButton
                    v-if="$route.name === 'config'"
                    @click="toggleLeftSidebar('config')"
                    :label="'config'"
                    :active="selectedSideBar === 'config'"
                    title="Toggle config menu"
                    class="p-0"
                >
                    <ProiconsMenu height="20" width="20" />
                </NavButton>
                <NavLink v-if="$route.name != 'home'" label="home" to="/" title="Return to home library" class="p-0">
                    <CircumMonitor class="size-6" />
                </NavLink>
            </span>
            <ToggleLightMode class="dark:hover:border-primary w-17 border-gray-900/5 shadow-sm" />
        </div>
        <hr class="text-hr block w-full shrink-0" />
    </nav>
</template>
