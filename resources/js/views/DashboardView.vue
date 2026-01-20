<script setup lang="ts">
import { useDashboardTabs } from '@/components/panels/DashboardTabs';
import { getScreenSize } from '@/service/util';
import { useAppStore } from '@/stores/AppStore';
import { onMounted } from 'vue';

import DashboardAnalytics from '@/components/dashboard/DashboardAnalytics.vue';
import DashboardLibraries from '@/components/dashboard/DashboardLibraries.vue';
import DashboardActivity from '@/components/dashboard/DashboardActivity.vue';
import DashboardSidebar from '@/components/panels/DashboardSidebar.vue';
import DashboardUsers from '@/components/dashboard/DashboardUsers.vue';
import DashboardTasks from '@/components/dashboard/DashboardTasks.vue';
import LayoutBase from '@/layouts/LayoutBase.vue';

const { activeDashboardTab } = useDashboardTabs();
const { cycleSideBar } = useAppStore();

onMounted(async () => {
    const screenSize = getScreenSize();

    if (screenSize === 'default' || screenSize === 'sm' || screenSize === 'md') return;

    cycleSideBar('dashboard', 'left-card', false);
});
</script>

<template>
    <LayoutBase>
        <template v-slot:content>
            <section id="content-dashboard" class="3xl:min-h-[60vh] flex flex-col gap-4 text-sm lg:min-h-[80vh]">
                <DashboardAnalytics v-if="activeDashboardTab?.name == 'overview'" />
                <DashboardLibraries v-if="activeDashboardTab?.name == 'libraries'" />
                <DashboardActivity v-if="activeDashboardTab?.name == 'activity'" />
                <DashboardUsers v-if="activeDashboardTab?.name == 'users'" />
                <DashboardTasks v-if="activeDashboardTab?.name == 'tasks'" />
            </section>
        </template>
        <template v-slot:leftSidebar>
            <DashboardSidebar />
        </template>
    </LayoutBase>
</template>
