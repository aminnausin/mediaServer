<script setup lang="ts">
import { onMounted, ref, watch, type Component } from 'vue';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';

import DashboardAnalytics from '@/components/dashboard/DashboardAnalytics.vue';
import DashboardLibraries from '@/components/dashboard/DashboardLibraries.vue';
import SidebarCard from '@/components/cards/SidebarCard.vue';
import LayoutBase from '@/layouts/LayoutBase.vue';

import LucideImages from '~icons/lucide/images';
import LucideChartNoAxesColumnIncreasing from '~icons/lucide/chart-no-axes-column-increasing';
import CircumDatabase from '~icons/circum/database';
import CircumServer from '~icons/circum/server';
import ProiconsLibrary from '~icons/proicons/library';
import ProiconsBookAdd2 from '~icons/proicons/book-add-2';
import ProiconsAddSquareMultiple from '~icons/proicons/add-square-multiple';
import LucideUsers from '~icons/lucide/users';
import ProiconsHistory from '~icons/proicons/history';

const dashboardTabs: { name: string; title?: string; icon?: Component }[] = [
    { name: 'overview', title: 'Website Analytics', icon: LucideChartNoAxesColumnIncreasing },
    { name: 'libraries', icon: ProiconsLibrary },
    { name: 'activity', icon: ProiconsHistory },
    { name: 'users', icon: LucideUsers },
    { name: 'jobs', icon: CircumServer },
];

const dashboardTab = ref<{ name: string; title?: string; icon?: any }>();

const route = useRoute();
const AppStore = useAppStore();
const { cycleSideBar } = AppStore;
const { pageTitle, selectedSideBar } = storeToRefs(AppStore);

onMounted(async () => {
    const URL_TAB = route.query.tab;
    let defaultTab = dashboardTabs.find((tab) => (tab.title ?? tab.name) == URL_TAB) ?? dashboardTabs[0];

    pageTitle.value = defaultTab.title ?? defaultTab.name;
    dashboardTab.value = defaultTab;
    cycleSideBar('dashboard', '#left-card');
});

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
                        :link="`/dashboard?tab=${tab.name}`"
                        :class="`
                            items-center justify-between !gap-2
                            capitalize overflow-hidden bg-white hover:bg-primary-800
                            ring-inset ring-purple-600 hover:ring-purple-600/50 hover:ring-[0.125rem] ${dashboardTab?.name == tab.name && 'ring-[0.125rem]'}
                        `"
                        @click="dashboardTab = tab"
                    >
                        <template #header>
                            <h2 class="w-full flex-1" :title="tab.title ?? tab.name">{{ tab.title ?? tab.name }}</h2>
                            <component v-if="tab.icon" :is="tab.icon" class="ml-auto w-6 h-6" />
                            <!-- <ButtonCorner
                                    :positionClasses="'w-7 h-7'"
                                    :textClasses="'hover:text-violet-600 dark:hover:text-violet-500'"
                                    :colourClasses="'dark:hover:bg-neutral-800 hover:bg-gray-300'"
                                    :label="'Share'"
                                    @click.stop.prevent="$emit('clickAction', link)"
                                >
                                    <template #icon>
                                        <CircumShare1 width="20" height="20" />
                                    </template>
                                </ButtonCorner> -->
                            <!-- <ButtonCorner
                                    :positionClasses="'w-7 h-7'"
                                    :textClasses="`${true ? 'text-violet-600' : 'hover:text-violet-600'} dark:hover:text-violet-500`"
                                    :colourClasses="'dark:hover:bg-neutral-800 hover:bg-gray-300'"
                                    :to="''"
                                    :label="'Open Folder'"
                                >
                                    <template #icon>
                                        <CircumFolderOn width="20" height="20" />
                                    </template>
                                </ButtonCorner> -->
                        </template>
                    </SidebarCard>
                </section>
            </div>
        </template>
    </LayoutBase>
</template>
