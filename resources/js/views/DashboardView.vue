<script setup lang="ts">
import type { AppManifest, TaskStatsResponse } from '@/types/types';

import { computed, onMounted, ref, watch, type Component, type Ref } from 'vue';
import { useDashboardStore } from '@/stores/DashboardStore';
import { useAuthStore } from '@/stores/AuthStore';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';

import DashboardAnalytics from '@/components/dashboard/DashboardAnalytics.vue';
import DashboardLibraries from '@/components/dashboard/DashboardLibraries.vue';
import DashboardActivity from '@/components/dashboard/DashboardActivity.vue';
import DashboardUsers from '@/components/dashboard/DashboardUsers.vue';
import DashboardTasks from '@/components/dashboard/DashboardTasks.vue';
import SidebarCard from '@/components/cards/SidebarCard.vue';
import LayoutBase from '@/layouts/LayoutBase.vue';

import ProiconsLibrary from '~icons/proicons/library';
import ProiconsServer from '~icons/proicons/server';
import ProiconsGithub from '~icons/proicons/github';
import ProiconsGraph from '~icons/proicons/graph';
import CircumServer from '~icons/circum/server';
import LucideUsers from '~icons/lucide/users';

const { stateTaskStats, stateTotalLibrariesSize, stateLibraryId, stateActiveSessions } = storeToRefs(useDashboardStore()) as {
    stateTaskStats: Ref<TaskStatsResponse>;
    stateTotalLibrariesSize: Ref<string>;
    stateLibraryId: Ref<number>;
    stateActiveSessions: Ref<number>;
};
const { pageTitle, selectedSideBar, appManifest } = storeToRefs(useAppStore()) as unknown as { pageTitle: Ref<any>; selectedSideBar: Ref<any>; appManifest: Ref<AppManifest> };
const { cycleSideBar } = useAppStore();
const { userData } = storeToRefs(useAuthStore());

const dashboardTab = ref<{ name: string; title?: string; icon?: any }>();

const dashboardTabs = computed<
    {
        name: string;
        title?: string;
        description?: string;
        info?: { value: string; icon?: Component };
        icon?: Component;
        disabled?: boolean;
    }[]
>(() => {
    return [
        {
            name: 'overview',
            title: 'Analytics',
            description: 'Website Overview',
            icon: ProiconsGraph,
        },
        {
            name: 'libraries',
            description: '',
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
            description: '',
            info: { value: `Logged In: ${stateActiveSessions?.value ?? '?'}` },
            icon: LucideUsers,
        },
        {
            name: 'tasks',
            description: '',
            info: { value: `Currently Running: ${stateTaskStats.value?.count_running ?? '?'}` },
            icon: CircumServer,
            disabled: userData.value?.id !== 1,
        },
    ];
});

const route = useRoute();

onMounted(async () => {
    cycleSideBar('dashboard', 'left-card');
});

watch(
    () => route?.params?.tab,
    (URL_TAB) => {
        if (!URL_TAB) return;
        let defaultTab = dashboardTabs.value.find((tab) => (tab.title ?? tab.name) == URL_TAB) ?? dashboardTabs.value[0];

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
            <section id="content-dashboard" class="min-h-[80vh]">
                <DashboardAnalytics v-if="dashboardTab?.name == 'overview'" />
                <DashboardLibraries v-if="dashboardTab?.name == 'libraries'" />
                <DashboardActivity v-if="dashboardTab?.name == 'activity'" />
                <DashboardUsers v-if="dashboardTab?.name == 'users'" />
                <DashboardTasks v-if="dashboardTab?.name == 'tasks'" />
            </section>
        </template>
        <template v-slot:leftSidebar>
            <div class="p-3 px-6 lg:px-3 flex flex-col gap-3">
                <div class="flex py-1 flex-col gap-2">
                    <h2 id="sidebar-title" class="text-2xl h-8 w-full capitalize dark:text-white">{{ selectedSideBar }}</h2>
                    <hr class="" />
                </div>
                <section class="flex flex-col gap-2 flex-1">
                    <SidebarCard
                        v-for="(tab, index) in dashboardTabs.filter((tab) => !tab.disabled)"
                        :key="index"
                        :link="tab.disabled ? '' : `/dashboard/${tab.name}`"
                        :class="`
                            items-center justify-between !gap-2
                            capitalize overflow-hidden bg-white hover:bg-primary-800
                            ring-inset ring-purple-600 hover:ring-purple-600/50 hover:ring-[0.125rem] ${dashboardTab?.name == tab.name && 'ring-[0.125rem]'}
                            aria-disabled:cursor-not-allowed aria-disabled:hover:ring-neutral-200 aria-disabled:hover:dark:ring-neutral-700  aria-disabled:opacity-60
                        `"
                        @click="
                            () => {
                                if (tab.disabled) return;
                                dashboardTab = tab;
                            }
                        "
                        :aria-disabled="tab.disabled"
                    >
                        <template #header>
                            <h3 class="w-full flex-1 text-gray-900 dark:text-white" :title="tab.title ?? tab.name">{{ tab.title ?? tab.name }}</h3>
                            <component v-if="tab.icon" :is="tab.icon" class="ml-auto w-6 h-6" />
                        </template>
                        <template #body>
                            <h4 v-if="tab.description" title="Description" class="text-neutral-500 w-full text-wrap truncate sm:text-nowrap flex-1">
                                {{ tab.description }}
                            </h4>
                            <h4 v-if="tab.info" title="Information" class="truncate text-nowrap sm:text-right text-neutral-500 w-fit">
                                <!-- some other folder statistic or data like number of seasons or if its popular or something -->
                                {{ tab.info.value }}
                            </h4>
                        </template>
                    </SidebarCard>

                    <SidebarCard
                        :to="`${appManifest?.commit ? `https://github.com/aminnausin/mediaServer/commit/${appManifest?.commit}` : ''}`"
                        :class="`
                            items-center justify-between text-sm
                            capitalize overflow-hidden bg-white hover:bg-primary-800
                            ring-inset ring-purple-600 hover:ring-purple-600/50 hover:ring-[0.125rem]
                            aria-disabled:cursor-not-allowed aria-disabled:hover:ring-neutral-200 aria-disabled:hover:dark:ring-neutral-700  aria-disabled:opacity-60
                        `"
                        @click=""
                        :aria-disabled="false"
                    >
                        <template #header>
                            <h3 class="text-gray-900 dark:text-white" :title="'Source Code'">#{{ appManifest.commit }}</h3>
                            <ProiconsGithub class="ml-auto w-6 h-6" />
                        </template>
                        <template #body>
                            <h4 title="Description" class="text-neutral-500 w-full text-wrap truncate sm:text-nowrap flex-1">MediaServer</h4>
                            <h4 v-if="appManifest.commit" title="Information" class="truncate text-nowrap sm:text-right text-neutral-500 w-fit">
                                {{ appManifest.version ?? 'V0.1.15b' }}
                            </h4>
                        </template>
                    </SidebarCard>
                </section>
            </div>
        </template>
    </LayoutBase>
</template>
