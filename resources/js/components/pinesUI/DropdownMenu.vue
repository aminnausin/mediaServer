<script setup lang="ts">
import type { DropdownMenuItem } from '@/types/types';

import { computed, h, nextTick, onMounted, onUnmounted, ref, useTemplateRef, watch } from 'vue';
import { toFormattedDuration } from '@/service/util';
import { handleStartTask } from '@/service/taskService';
import { useContentStore } from '@/stores/ContentStore';
import { OnClickOutside } from '@vueuse/components';
import { useAuthStore } from '@/stores/AuthStore';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';

import DropdownItem from '@/components/pinesUI/DropdownItem.vue';

import LucideLayoutDashboard from '~icons/lucide/layout-dashboard';
import LucideTvMinimalPlay from '~icons/lucide/tv-minimal-play';
import LucideFolderSearch from '~icons/lucide/folder-search';
import LucideFolderCheck from '~icons/lucide/folder-check';
import ProiconsTaskList from '~icons/proicons/task-list';
import ProiconsSettings from '~icons/proicons/settings';
import LucideFolderSync from '~icons/lucide/folder-sync';
import ProiconsLibrary from '~icons/proicons/library';
import ProiconsSpinner from '~icons/proicons/spinner';
import LucideUserPlus from '~icons/lucide/user-plus';
import ProiconsScript from '~icons/proicons/script';
import ProiconsGraph from '~icons/proicons/graph';
import LucideHistory from '~icons/lucide/history';
import LucideLogOut from '~icons/lucide/log-out';
import LucideLogIn from '~icons/lucide/log-in';
import LucideUsers from '~icons/lucide/users';
import LucideUser from '~icons/lucide/user';

const defaults = { external: false, disabled: false };

const dropDownItems: DropdownMenuItem[][] = [
    [{ ...defaults, name: 'settings', url: '/settings', text: 'Settings', icon: ProiconsSettings }],
    [
        { ...defaults, name: 'login', url: '/login', text: 'Log in', icon: LucideLogIn },
        { ...defaults, name: 'register', url: '/register', text: 'Sign up', icon: LucideUserPlus },
    ],
];

const { taskWaitTimes, isLoadingWaitTimes } = storeToRefs(useAppStore());
const { stateDirectory } = storeToRefs(useContentStore());
const { userData } = storeToRefs(useAuthStore());

const props = defineProps<{ dropdownOpen: boolean }>();
const dropdown = useTemplateRef('dropdown');
const manualPosition = ref(0);

const dropDownItemsAuth = computed<DropdownMenuItem[][]>(() => {
    return [
        [
            { ...defaults, name: 'profile', url: '/profile', text: 'Account', icon: LucideUser, disabled: false, iconStrokeWidth: 2 },
            { ...defaults, name: 'settings', url: '/settings', text: 'Settings', icon: ProiconsSettings, iconStrokeWidth: 2 },
            { ...defaults, name: 'home', url: '/', text: 'Home', icon: LucideTvMinimalPlay },
        ],
        [
            { ...defaults, name: 'friends', url: '/friends', text: 'Friends', icon: LucideUsers, disabled: true },
            { ...defaults, name: 'history', url: '/history', text: 'Full History', icon: LucideHistory },
            { ...defaults, name: 'overview', url: '/dashboard', text: 'Insights', icon: LucideLayoutDashboard, disabled: true },
        ],
        [
            { ...defaults, name: 'overview', url: '/dashboard/overview', text: 'Analytics', icon: ProiconsGraph },
            { ...defaults, name: 'libraries', url: '/dashboard/libraries', text: 'Libraries', icon: ProiconsLibrary },
            { ...defaults, name: 'users', url: '/dashboard/users', text: 'Users', icon: LucideUsers },
            { ...defaults, name: 'tasks', url: '/dashboard/tasks', text: 'Tasks', icon: ProiconsTaskList, hidden: userData.value?.id !== 1 },
            { ...defaults, name: 'logs', url: '/log-viewer', text: 'Logs', icon: ProiconsScript, hidden: userData.value?.id !== 1, external: true },
        ],
        [
            {
                ...defaults,
                name: 'index',
                text: 'Index Media',
                shortcut: isLoadingWaitTimes.value
                    ? h(ProiconsSpinner, { class: 'animate-spin' })
                    : taskWaitTimes.value?.scan
                      ? `~${toFormattedDuration(taskWaitTimes.value?.index, false, 'analog', true)}`
                      : '',
                shortcutTitle: 'Estimated Time',
                title: 'Search for changes in all media',
                icon: LucideFolderSearch,
                action: () => {
                    handleStartTask('index');
                },
            },
            {
                ...defaults,
                name: 'sync',
                text: 'Sync Media',
                title: 'Sync database media information with the server',
                hidden: true,
                icon: LucideFolderSync,
                action: () => {
                    handleStartTask('sync');
                },
            },
            {
                ...defaults,
                name: 'Scan media',
                text: 'Scan Media',
                shortcut: isLoadingWaitTimes.value
                    ? h(ProiconsSpinner, { class: 'animate-spin' })
                    : taskWaitTimes.value?.scan
                      ? `~${toFormattedDuration(taskWaitTimes.value?.scan, false, 'analog', true)}`
                      : '',
                shortcutTitle: 'Estimated Time',
                title: 'Search for changes and verify metadata for all media', // (titles, descriptions, duration, filesize, thumbnails, audio metadata, external metadata)',
                icon: LucideFolderCheck,
                action: () => {
                    handleStartTask('scan');
                },
            },

            {
                ...defaults,
                name: 'verify folders',
                text: 'Verify Folders',
                title: 'Verify folder metadata (titles, video counts, folder size, localised thumbnails)',
                hidden: false,
                icon: LucideFolderSync,
                shortcut: isLoadingWaitTimes.value
                    ? h(ProiconsSpinner, { class: 'animate-spin' })
                    : taskWaitTimes.value?.verify_folders
                      ? `~${toFormattedDuration(taskWaitTimes.value?.verify_folders, false, 'analog', true)}`
                      : '',
                shortcutTitle: 'Estimated Time',
                action: () => {
                    handleStartTask('verifyFolders');
                },
            },
        ],
        [
            {
                ...defaults,
                name: 'Scan library',
                text: 'Scan Library',
                title: 'Search for changes and verify metadata for media in this library', // (titles, descriptions, duration, filesize, thumbnails, audio metadata, external metadata)
                icon: LucideFolderCheck,
                hidden: !stateDirectory.value || stateDirectory.value.id < 1,
                action: () => {
                    if ((stateDirectory.value?.id ?? 0) < 1) return;
                    handleStartTask('scan', stateDirectory.value?.id);
                },
            },
            {
                ...defaults,
                name: 'verify library',
                text: 'Verify Library',
                title: 'Verify media and folder metadata (titles, video counts, folder size, localised thumbnails)',
                hidden: true,
                icon: LucideFolderSync,
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
            leave-from-class="translate-y-0"
            leave-to-class="-translate-y-4 opacity-0"
        >
            <div
                v-show="props.dropdownOpen"
                :class="`absolute top-0 z-50 mx-auto mt-12 w-56 max-w-[80vw] ${manualPosition ? '' : '-right-1'} `"
                v-cloak
                id="user-dropdown"
                role="menu"
                :style="manualPosition ? `left: ${manualPosition}px;` : ''"
                ref="dropdown"
            >
                <div
                    class="mt-1 rounded-md border border-neutral-200/70 bg-white p-1 text-neutral-700 shadow-md backdrop-blur-lg dark:border-neutral-700 dark:bg-neutral-800/70 dark:text-neutral-100"
                >
                    <div class="px-2 py-1.5 text-sm font-semibold" v-if="userData">{{ userData.email }}</div>
                    <div class="-mx-1 my-1 h-px bg-neutral-200 dark:bg-neutral-500" v-if="userData"></div>
                    <section v-for="(group, groupIndex) in userData ? dropDownItemsAuth : dropDownItems" :key="groupIndex">
                        <div
                            v-if="groupIndex !== 0 && groupIndex !== group.length && group.some((item) => !item.hidden)"
                            class="-mx-1 my-1 h-px bg-neutral-200 dark:bg-neutral-500"
                        ></div>
                        <DropdownItem
                            v-for="(item, index) in group.filter((item) => !item.hidden)"
                            :key="index"
                            :linkData="item"
                            :selected="$route.name === item.name || $route.path === item.name || $route.path === item.url"
                            :external="item.external"
                            :disabled="item.disabled ?? false"
                            @click="
                                () => {
                                    $emit('toggleDropdown', false);
                                    if (item.action && userData) item.action();
                                }
                            "
                        >
                            <template #icon>
                                <component :is="item.icon" :class="['mr-2 h-4 w-4', item.iconStrokeWidth ? `[&>*]:stroke-[${item.iconStrokeWidth}]` : '']" />
                            </template>
                        </DropdownItem>
                    </section>
                </div>
            </div>
        </Transition>
    </OnClickOutside>
</template>
