<script setup lang="ts">
import type { TaskStatsResponse } from '@/types/types';

import { computed, onMounted, ref, watch, type Component, type Ref } from 'vue';
import { useDashboardStore } from '@/stores/DashboardStore';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';

import DashboardAnalytics from '@/components/dashboard/DashboardAnalytics.vue';
import DashboardLibraries from '@/components/dashboard/DashboardLibraries.vue';
import SidebarCard from '@/components/cards/SidebarCard.vue';
import LayoutBase from '@/layouts/LayoutBase.vue';

import LucideChartNoAxesColumnIncreasing from '~icons/lucide/chart-no-axes-column-increasing';
import ProiconsAddSquareMultiple from '~icons/proicons/add-square-multiple';
import ProiconsBookAdd2 from '~icons/proicons/book-add-2';
import ProiconsLibrary from '~icons/proicons/library';
import ProiconsHistory from '~icons/proicons/history';
import CircumDatabase from '~icons/circum/database';
import ProiconsGraph from '~icons/proicons/graph';
import LucideImages from '~icons/lucide/images';
import CircumServer from '~icons/circum/server';
import LucideUsers from '~icons/lucide/users';
import DashboardActivity from '@/components/dashboard/DashboardActivity.vue';
import DashboardUsers from '@/components/dashboard/DashboardUsers.vue';
import DashboardTasks from '@/components/dashboard/DashboardTasks.vue';
import { useAuthStore } from '@/stores/AuthStore';

const { stateTaskStats, stateTotalLibrariesSize } = storeToRefs(useDashboardStore()) as { stateTaskStats: Ref<TaskStatsResponse>; stateTotalLibrariesSize: Ref<string> };
const { pageTitle, selectedSideBar } = storeToRefs(useAppStore());
const { cycleSideBar } = useAppStore();
const { userData } = storeToRefs(useAuthStore());
const route = useRoute();

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
            disabled: userData.value?.id !== 1,
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
            info: { value: 'Logged In: ?' },
            icon: LucideUsers,
            disabled: userData.value?.id !== 1,
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
            <div class="p-3 flex flex-col gap-3">
                <div class="flex py-1 flex-col gap-2">
                    <h1 id="sidebar-title" class="text-2xl h-8 w-full capitalize dark:text-white">{{ selectedSideBar }}</h1>
                    <hr class="" />
                </div>
                <section class="flex flex-col gap-2">
                    <SidebarCard
                        v-for="(tab, index) in dashboardTabs"
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
                            <h2 class="w-full flex-1" :title="tab.title ?? tab.name">{{ tab.title ?? tab.name }}</h2>
                            <component v-if="tab.icon" :is="tab.icon" class="ml-auto w-6 h-6" />
                        </template>
                        <template #body>
                            <h3 v-if="tab.description" title="Description" class="text-neutral-500 w-full text-wrap truncate sm:text-nowrap flex-1">
                                {{ tab.description }}
                            </h3>
                            <h3 v-if="tab.info" title="Information" class="truncate text-nowrap sm:text-right text-neutral-500 w-fit">
                                <!-- some other folder statistic or data like number of seasons or if its popular or something -->
                                {{ tab.info.value }}
                            </h3>
                        </template>
                    </SidebarCard>
                </section>
            </div>
        </template>
    </LayoutBase>
</template>
