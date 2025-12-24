<script setup lang="ts">
import type { SidebarTabItem, TaskStatsResponse } from '@/types/types';
import type { Ref } from 'vue';

import { computed, onMounted, ref, watch } from 'vue';
import { useDashboardStore } from '@/stores/DashboardStore';
import { useAuthStore } from '@/stores/AuthStore';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';

import DashboardAnalytics from '@/components/dashboard/DashboardAnalytics.vue';
import DashboardLibraries from '@/components/dashboard/DashboardLibraries.vue';
import DashboardActivity from '@/components/dashboard/DashboardActivity.vue';
import AppManifestCard from '@/components/cards/AppManifestCard.vue';
import DashboardUsers from '@/components/dashboard/DashboardUsers.vue';
import DashboardTasks from '@/components/dashboard/DashboardTasks.vue';
import SidebarHeader from '@/components/headers/SidebarHeader.vue';
import SidebarCard from '@/components/cards/SidebarCard.vue';
import LayoutBase from '@/layouts/LayoutBase.vue';

import ProiconsSettings from '~icons/proicons/settings';
import ProiconsLibrary from '~icons/proicons/library';
import ProiconsGraph from '~icons/proicons/graph';
import CircumServer from '~icons/circum/server';
import LucideUsers from '~icons/lucide/users';

const { stateTaskStats, stateTotalLibrariesSize, stateLibraryId, stateActiveSessions } = storeToRefs(useDashboardStore()) as {
    stateTaskStats: Ref<TaskStatsResponse>;
    stateTotalLibrariesSize: Ref<string>;
    stateLibraryId: Ref<number>;
    stateActiveSessions: Ref<number>;
};
const { pageTitle, selectedSideBar } = storeToRefs(useAppStore());
const { cycleSideBar } = useAppStore();
const { userData } = storeToRefs(useAuthStore());

const dashboardTab = ref<{ name: string; title?: string; icon?: any }>();

const dashboardTabs = computed<SidebarTabItem[]>(() => {
    return [
        {
            name: 'overview',
            title: 'Analytics',
            description: 'Website Overview',
            icon: ProiconsGraph,
        },
        {
            name: 'libraries',
            title: 'Content Libraries',
            info: { value: `Total Size: ${stateTotalLibrariesSize?.value ?? '?'}` },
            icon: ProiconsLibrary,
        },
        // {
        //     name: 'activity',
        //     description: '',
        //     info: { value: 'Logged Events: 686' },
        //     icon: ProiconsHistory,
        // },
        {
            name: 'users',
            title: 'User Management',
            info: { value: `Logged In: ${stateActiveSessions?.value ?? '?'}` },
            icon: LucideUsers,
        },
        {
            name: 'tasks',
            title: 'Task Management',
            info: { value: `Currently Running: ${stateTaskStats.value?.count_running ?? '?'}` },
            icon: CircumServer,
            disabled: userData.value?.id !== 1,
        },
    ];
});

const route = useRoute();

onMounted(async () => {
    cycleSideBar('dashboard', 'left-card', false);
});

watch(
    () => route?.params?.tab,
    (URL_TAB) => {
        if (!URL_TAB) return;
        const defaultTab = dashboardTabs.value.find((tab) => tab.title === URL_TAB || tab.name === URL_TAB) ?? dashboardTabs.value[0];

        pageTitle.value = defaultTab.title ?? defaultTab.name;
        dashboardTab.value = defaultTab;
    },
    { immediate: true },
);

watch(
    () => dashboardTab.value,
    () => {
        stateLibraryId.value = -1;
        if (!dashboardTab.value) return;
        pageTitle.value = dashboardTab.value.title ?? dashboardTab.value.name;
    },
);
</script>

<template>
    <LayoutBase>
        <template v-slot:content>
            <section id="content-dashboard" class="3xl:min-h-[60vh] flex flex-col gap-4 text-sm lg:min-h-[80vh]">
                <DashboardAnalytics v-if="dashboardTab?.name == 'overview'" />
                <DashboardLibraries v-if="dashboardTab?.name == 'libraries'" />
                <DashboardActivity v-if="dashboardTab?.name == 'activity'" />
                <DashboardUsers v-if="dashboardTab?.name == 'users'" />
                <DashboardTasks v-if="dashboardTab?.name == 'tasks'" />
            </section>
        </template>
        <template v-slot:leftSidebar>
            <SidebarHeader />

            <section class="flex flex-1 flex-col gap-2">
                <SidebarCard
                    v-for="(tab, index) in dashboardTabs.filter((tab) => !tab.disabled)"
                    :key="index"
                    :link="tab.disabled ? '' : `/dashboard/${tab.name}`"
                    :class="[
                        'items-center justify-between gap-2!',
                        'hover:bg-primary-800 overflow-hidden bg-white capitalize',
                        `ring-purple-600 ring-inset hover:ring-2 hover:ring-purple-600/50 ${dashboardTab?.name == tab.name && 'ring-2'}`,
                        'aria-disabled:cursor-not-allowed aria-disabled:opacity-60 aria-disabled:hover:ring-neutral-200 dark:aria-disabled:hover:ring-neutral-700',
                    ]"
                    @click="
                        () => {
                            if (tab.disabled) return;
                            dashboardTab = tab;
                        }
                    "
                    :aria-disabled="tab.disabled"
                >
                    <template #header>
                        <h3 class="line-clamp-1 w-full flex-1 text-gray-900 dark:text-white" :title="tab.title ?? tab.name">{{ tab.title ?? tab.name }}</h3>
                        <component v-if="tab.icon" :is="tab.icon" class="ml-auto h-6 w-6" />
                    </template>
                    <template #body>
                        <h4 v-if="tab.description" title="Description" class="w-full flex-1 truncate text-wrap sm:text-nowrap">
                            {{ tab.description }}
                        </h4>
                        <h4 v-if="tab.info" title="Information" class="w-fit truncate text-nowrap sm:text-right">
                            {{ tab.info.value }}
                        </h4>
                    </template>
                </SidebarCard>

                <SidebarCard
                    :link="`/settings`"
                    :class="[
                        'items-center justify-between',
                        'hover:bg-primary-800 overflow-hidden bg-white capitalize',
                        'ring-purple-600 ring-inset hover:ring-2 hover:ring-purple-600/50',
                        'aria-disabled:cursor-not-allowed aria-disabled:opacity-60 aria-disabled:hover:ring-neutral-200 dark:aria-disabled:hover:ring-neutral-700',
                    ]"
                    :aria-disabled="false"
                >
                    <template #header>
                        <h3 class="text-gray-900 dark:text-white" :title="'Settings'">Settings</h3>
                        <ProiconsSettings class="ml-auto h-6 w-6" />
                    </template>
                    <template #body>
                        <h4 title="Description" class="w-full flex-1 truncate text-wrap sm:text-nowrap">Configurable Options</h4>
                    </template>
                </SidebarCard>
                <AppManifestCard />
            </section>
        </template>
    </LayoutBase>
</template>
