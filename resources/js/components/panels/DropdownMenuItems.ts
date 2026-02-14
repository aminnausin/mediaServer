import type { DropdownMenuItem } from '@aminnausin/cedar-ui';

import { toFormattedDuration } from '@/service/util';
import { useContentStore } from '@/stores/ContentStore';
import { handleStartTask } from '@/service/taskService';
import { useAuthStore } from '@/stores/AuthStore';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { h, computed } from 'vue';

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

export function useDropdownMenuItems() {
    const defaults = { external: false, disabled: false };

    const { taskWaitTimes, isLoadingWaitTimes } = storeToRefs(useAppStore());
    const { stateDirectory } = storeToRefs(useContentStore());
    const { userData } = storeToRefs(useAuthStore());

    const taskIcons = computed(() => {
        const loadingIcon = h(ProiconsSpinner, { class: 'animate-spin' });

        const formatWaitTime = (value?: number) => {
            if (value) return `~${toFormattedDuration(value, false, 'analog', true)}`;
            return '';
        };
        if (isLoadingWaitTimes.value) {
            return {
                index: loadingIcon,
                scan: loadingIcon,
                verify: loadingIcon,
            };
        }

        return {
            index: formatWaitTime(taskWaitTimes.value.index),
            scan: formatWaitTime(taskWaitTimes.value.scan),
            verify: formatWaitTime(taskWaitTimes.value.scan),
        };
    });

    const dropdownItems: DropdownMenuItem[][] = [
        [{ ...defaults, name: 'settings', url: '/settings', text: 'Settings', icon: ProiconsSettings }],
        [
            { ...defaults, name: 'login', url: '/login', text: 'Log in', icon: LucideLogIn },
            { ...defaults, name: 'register', url: '/register', text: 'Sign up', icon: LucideUserPlus },
        ],
    ];

    const dropdownItemsAuth = computed<DropdownMenuItem[][]>(() => {
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
                    shortcut: taskIcons.value.index,
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
                    shortcut: taskIcons.value.scan,
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
                    shortcut: taskIcons.value.verify,
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
            [{ ...defaults, name: 'logout', url: '/logout', text: 'Log out', icon: LucideLogOut }], // ⇧⌘Q
        ];
    });

    return {
        dropdownItemsAuth,
        dropdownItems,
    };
}
