<script setup>
import DropdownItem from './DropdownItem.vue';

import LucideSettings from '~icons/lucide/settings';
import LucideUser from '~icons/lucide/user';
import LucideUsers from '~icons/lucide/users';
import LucideUserPlus from '~icons/lucide/user-plus';
import LucideTvMinimalPlay from '~icons/lucide/tv-minimal-play';
import LucideHistory from '~icons/lucide/history';
import LucideLogIn from '~icons/lucide/log-in';
import LucideLogOut from '~icons/lucide/log-out';
import LucideFolderSync from '~icons/lucide/folder-sync';
import LucideFolderSearch from '~icons/lucide/folder-search';
import LucideFolderCheck from '~icons/lucide/folder-check';
import LucideLayoutDashboard from '~icons/lucide/layout-dashboard';

import { OnClickOutside } from '@vueuse/components';
import { useAuthStore } from '../../stores/AuthStore';
import { storeToRefs } from 'pinia';

const authStore = useAuthStore();
const { userData } = storeToRefs(authStore);

const dropDownItems = [
    [{ name: 'settings', url: '/settings', text: 'Settings', icon: LucideSettings }],
    [
        { name: 'login', url: '/login', text: 'Log in', icon: LucideLogIn },
        { name: 'register', url: '/register', text: 'Sign up', icon: LucideUserPlus },
    ],
];
const dropDownItemsAuth = [
    [
        { name: 'profile', url: '/profile', text: 'Profile', icon: LucideUser },
        { name: 'settings', url: '/settings', text: 'Settings', icon: LucideSettings, disabled: true },
        { name: 'home', url: '/', text: 'Home', icon: LucideTvMinimalPlay },
    ],
    [
        { name: 'friends', url: '/friends', text: 'Friends', icon: LucideUsers, disabled: true },
        { name: 'history', url: '/history', text: 'Full History', icon: LucideHistory },
        { name: 'dashboard', url: '/Dashboard', text: 'Dashboard', icon: LucideLayoutDashboard },
    ],
    [
        { name: 'index', url: '/jobs/indexFiles', text: 'Index Files', external: true, icon: LucideFolderSearch },
        { name: 'sync', url: '/jobs/syncFiles', text: 'Sync Files', external: true, icon: LucideFolderSync },
        { name: 'verify', url: '/jobs/verifyFiles', text: 'Verify Files', external: true, icon: LucideFolderCheck },
    ],
    [{ name: 'logout', url: '/logout', text: 'Log out', icon: LucideLogOut, shortcut: '⇧⌘Q' }],
];

const props = defineProps(['dropdownOpen']);
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
            <div v-show="props.dropdownOpen" class="absolute top-0 z-50 max-w-screen mt-12 -right-[0.25rem]" v-cloak id="userDropdown">
                <div class="w-56 mx-auto">
                    <div
                        v-if="userData"
                        class="p-1 mt-1 bg-white dark:bg-neutral-800/70 backdrop-blur-lg border rounded-md shadow-md border-neutral-200/70 dark:border-neutral-700 text-neutral-700 dark:text-neutral-100"
                    >
                        <div class="px-2 py-1.5 text-sm font-semibold">My Account</div>
                        <div class="h-px my-1 -mx-1 bg-neutral-200 dark:bg-neutral-500"></div>
                        <section v-for="(group, groupIndex) in dropDownItemsAuth" :key="groupIndex">
                            <div
                                v-if="groupIndex !== 0 && groupIndex !== dropDownItemsAuth.length"
                                class="h-px my-1 -mx-1 bg-neutral-200 dark:bg-neutral-500"
                            ></div>
                            <DropdownItem
                                v-for="(item, index) in dropDownItemsAuth[groupIndex]"
                                :key="index"
                                :linkData="item"
                                :selected="$route.name === item.name"
                                :external="item?.external"
                                :disabled="item?.disabled ?? false"
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
                    <div
                        v-else
                        class="p-1 mt-1 bg-white dark:bg-neutral-800/70 backdrop-blur-lg border rounded-md shadow-md border-neutral-200/70 dark:border-neutral-700 text-neutral-700 dark:text-neutral-100"
                    >
                        <section v-for="(group, groupIndex) in dropDownItems" :key="groupIndex">
                            <div
                                v-if="groupIndex !== 0 && groupIndex !== dropDownItems.length"
                                class="h-px my-1 -mx-1 bg-neutral-200 dark:bg-neutral-500"
                            ></div>
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
