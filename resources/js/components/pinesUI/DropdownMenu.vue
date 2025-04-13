<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, useTemplateRef, watch, type Component } from 'vue';

import { handleStartTask } from '@/service/taskService';
import { OnClickOutside } from '@vueuse/components';
import { useAuthStore } from '@/stores/AuthStore';
import { storeToRefs } from 'pinia';

import DropdownItem from '@/components/pinesUI/DropdownItem.vue';

import LucideLayoutDashboard from '~icons/lucide/layout-dashboard';
import LucideTvMinimalPlay from '~icons/lucide/tv-minimal-play';
import LucideFolderSearch from '~icons/lucide/folder-search';
import LucideFolderCheck from '~icons/lucide/folder-check';
import LucideFolderSync from '~icons/lucide/folder-sync';
import CircumHardDrive from '~icons/circum/hard-drive';
import ProiconsLibrary from '~icons/proicons/library';
import LucideUserPlus from '~icons/lucide/user-plus';
import ProiconsScript from '~icons/proicons/script';
import LucideSettings from '~icons/lucide/settings';
import ProiconsGraph from '~icons/proicons/graph';
import LucideHistory from '~icons/lucide/history';
import LucideLogOut from '~icons/lucide/log-out';
import LucideLogIn from '~icons/lucide/log-in';
import LucideUsers from '~icons/lucide/users';
import LucideUser from '~icons/lucide/user';

const { userData } = storeToRefs(useAuthStore());

const defaults = { external: false, disabled: false };
const props = defineProps<{ dropdownOpen: boolean }>();
const manualPosition = ref(0);
const dropdown = useTemplateRef('dropdown');

const dropDownItems = [
    [{ ...defaults, name: 'settings', url: '/settings', text: 'Settings', icon: LucideSettings }],
    [
        { ...defaults, name: 'login', url: '/login', text: 'Log in', icon: LucideLogIn },
        { ...defaults, name: 'register', url: '/register', text: 'Sign up', icon: LucideUserPlus },
    ],
];

const dropDownItemsAuth = computed<
    {
        name: string;
        url?: string;
        text: string;
        icon?: Component;
        disabled?: boolean;
        hidden?: boolean;
        external?: boolean;
        action?: () => void;
        shortcut?: string;
    }[][]
>(() => {
    return [
        [
            { ...defaults, name: 'profile', url: '/profile', text: 'Profile', icon: LucideUser, disabled: true },
            { ...defaults, name: 'settings', url: '/settings', text: 'Settings', icon: LucideSettings },
            { ...defaults, name: 'home', url: '/', text: 'Home', icon: LucideTvMinimalPlay },
        ],
        [
            { ...defaults, name: 'friends', url: '/friends', text: 'Friends', icon: LucideUsers, disabled: true },
            { ...defaults, name: 'history', url: '/history', text: 'Full History', icon: LucideHistory },
            { ...defaults, name: 'overview', url: '/dashboard', text: 'Dashboard', icon: LucideLayoutDashboard, disabled: true },
        ],
        [
            { ...defaults, name: 'overview', url: '/dashboard/overview', text: 'Analytics', icon: ProiconsGraph },
            { ...defaults, name: 'libraries', url: '/dashboard/libraries', text: 'Libraries', icon: ProiconsLibrary },
            { ...defaults, name: 'users', url: '/dashboard/users', text: 'Users', icon: LucideUsers },
            { ...defaults, name: 'tasks', url: '/dashboard/tasks', text: 'Tasks', icon: CircumHardDrive, hidden: userData.value?.id !== 1 },
            { ...defaults, name: 'logs', url: '/log-viewer', text: 'Logs', icon: ProiconsScript, hidden: userData.value?.id !== 1, external: true },
        ],
        [
            {
                ...defaults,
                name: 'index',
                text: 'Index Files',
                icon: LucideFolderSearch,
                action: () => {
                    handleStartTask('index');
                },
            },
            {
                ...defaults,
                name: 'sync',
                text: 'Sync Files',
                icon: LucideFolderSync,
                action: () => {
                    handleStartTask('sync');
                },
            },
            {
                ...defaults,
                name: 'verify',
                text: 'Verify Files',
                icon: LucideFolderCheck,
                action: () => {
                    handleStartTask('verify');
                },
            },
        ],
        [{ ...defaults, name: 'logout', url: '/logout', text: 'Log out', icon: LucideLogOut, shortcut: '⇧⌘Q' }],
    ];
});

const adjustDropdownPosition = async () => {
    if (!dropdown.value || !dropdown.value.parentElement) return;

    const parentRect = dropdown.value.parentElement.getBoundingClientRect();

    await nextTick();

    if (parentRect.right - 4 - dropdown.value.offsetWidth >= 0) {
        manualPosition.value = 0;
        return;
    }
    manualPosition.value = -parentRect.left + 20;
};

watch(
    () => props.dropdownOpen,
    (value) => {
        if (!value) return;
        adjustDropdownPosition();
    },
);

onMounted(() => {
    window.addEventListener('resize', adjustDropdownPosition);
});

onUnmounted(() => {
    window.removeEventListener('resize', adjustDropdownPosition);
});
</script>

<template>
    <OnClickOutside @trigger="$emit('toggleDropdown', false)">
        <slot name="trigger"></slot>
        <Transition
            enter-active-class="ease-out duration-200"
            enter-from-class="-translate-y-4"
            enter-to-class="translate-y-0"
            leave-active-class="ease-in duration-100"
            leave-from-class="-translate-y-0"
            leave-to-class="-translate-y-4 opacity-0"
        >
            <div
                v-show="props.dropdownOpen"
                :class="`absolute top-0 z-50 mt-12 ${manualPosition ? '' : '-right-[0.25rem]'} `"
                v-cloak
                id="user-dropdown"
                role="menu"
                :style="manualPosition ? `left: ${manualPosition}px;` : ''"
                ref="dropdown"
            >
                <div class="w-56 max-w-[80vw] mx-auto">
                    <div
                        v-if="userData"
                        class="p-1 mt-1 bg-white dark:bg-neutral-800/70 backdrop-blur-lg border rounded-md shadow-md border-neutral-200/70 dark:border-neutral-700 text-neutral-700 dark:text-neutral-100"
                    >
                        <div class="px-2 py-1.5 text-sm font-semibold">My Account</div>
                        <div class="h-px my-1 -mx-1 bg-neutral-200 dark:bg-neutral-500"></div>
                        <section v-for="(group, groupIndex) in dropDownItemsAuth" :key="groupIndex">
                            <div v-if="groupIndex !== 0 && groupIndex !== dropDownItemsAuth.length" class="h-px my-1 -mx-1 bg-neutral-200 dark:bg-neutral-500"></div>
                            <DropdownItem
                                v-for="(item, index) in dropDownItemsAuth[groupIndex].filter((item) => !item.hidden)"
                                :key="index"
                                :linkData="item"
                                :selected="item?.url && ($route.path === item.name || $route.path === item.url) ? true : false"
                                :external="item?.external ? true : false"
                                :disabled="item?.disabled ?? false"
                                @click="
                                    () => {
                                        $emit('toggleDropdown', false);
                                        if (item.action) item.action();
                                    }
                                "
                            >
                                <template #icon>
                                    <component :is="item.icon" viewBox="0 0 24 24" stroke="currentColor" stroke-width="0.2" class="w-4 h-4 mr-2" />
                                </template>
                            </DropdownItem>
                        </section>
                    </div>
                    <div
                        v-else
                        class="p-1 mt-1 bg-white dark:bg-neutral-800/70 backdrop-blur-lg border rounded-md shadow-md border-neutral-200/70 dark:border-neutral-700 text-neutral-700 dark:text-neutral-100"
                    >
                        <section v-for="(group, groupIndex) in dropDownItems" :key="groupIndex">
                            <div v-if="groupIndex !== 0 && groupIndex !== dropDownItems.length" class="h-px my-1 -mx-1 bg-neutral-200 dark:bg-neutral-500"></div>
                            <DropdownItem
                                v-for="(item, index) in dropDownItems[groupIndex]"
                                :key="index"
                                :linkData="item"
                                :selected="$route.name === item.name"
                                :external="item?.external"
                            >
                                <template #icon>
                                    <component
                                        :is="item.icon"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="w-4 h-4 mr-2"
                                    />
                                </template>
                            </DropdownItem>
                        </section>
                    </div>
                </div>
            </div>
        </Transition>
    </OnClickOutside>
</template>

<style>
.v-select.drop-up.vs--open .vs__dropdown-toggle {
    border-radius: 0 0 4px 4px;
    border-top-color: transparent;
    border-bottom-color: rgba(60, 60, 60, 0.26);
}
</style>
