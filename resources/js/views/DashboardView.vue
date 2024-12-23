<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';

import DashboardAnalytics from '@/components/dashboard/DashboardAnalytics.vue';
import DashboardLibraries from '@/components/dashboard/DashboardLibraries.vue';
import LayoutBase from '@/layouts/LayoutBase.vue';

const dashboardPages = [
    { title: 'overview', timeControls: true },
    { title: 'libraries', timeControls: false },
    { title: 'activity', timeControls: false },
    { title: 'users', timeControls: false },
    { title: 'jobs', timeControls: false },
];

const dashboardPage = ref('overview');

const AppStore = useAppStore();
const { cycleSideBar } = AppStore;
const { pageTitle } = storeToRefs(AppStore);

onMounted(async () => {
    pageTitle.value = 'Dashboard';
    cycleSideBar('dashboard', '#left-card');
});
</script>

<template>
    <LayoutBase>
        <template v-slot:content>
            <section id="content-dashboard" class="min-h-[80vh]">
                <DashboardAnalytics v-if="dashboardPage == 'overview'" />
                <DashboardLibraries v-if="dashboardPage == 'libraries'" />
            </section>
        </template>
        <template v-slot:leftSidebar>
            <div class="p-3 flex flex-col gap-3">
                <div class="flex py-1 flex-col gap-2">
                    <h1 id="sidebar-title" class="text-2xl h-8 w-full capitalize dark:text-white">{{ 'Management' }}</h1>
                    <hr class="" />
                </div>
                <section class="sm:text-lg flex flex-col gap-2">
                    <div
                        v-for="(page, index) in dashboardPages"
                        :key="index"
                        :class="`
                            flex flex-wrap items-center justify-between gap-2
                            rounded-lg shadow p-3 group cursor-pointer
                            capitalize dark:text-white overflow-hidden
                            dark:bg-primary-dark-800/70 bg-primary-800 dark:hover:bg-primary-dark-600 hover:bg-gray-200
                            ring-inset ring-purple-600/50 hover:ring-purple-600 hover:ring-[0.125rem] ${dashboardPage == page.title && 'ring-[0.125rem]'}
                        `"
                        @click="dashboardPage = page.title"
                    >
                        {{ page.title }}
                    </div>
                </section>
            </div>
        </template>
    </LayoutBase>
</template>
