<script setup lang="ts">
import { computed, defineAsyncComponent, onMounted } from 'vue';
import { useDashboardTabs } from '@/components/panels/DashboardTabs';
import { getScreenSize } from '@/service/util';
import { useAppStore } from '@/stores/AppStore';

import DashboardSkeleton from '@/components/dashboard/DashboardSkeleton.vue';
import DashboardSidebar from '@/components/panels/DashboardSidebar.vue';
import LayoutBase from '@/layouts/LayoutBase.vue';

const { activeDashboardTab } = useDashboardTabs();
const { cycleSideBar } = useAppStore();

const DashboardAnalytics = defineAsyncComponent(() => import('@/components/dashboard/DashboardAnalytics.vue'));
const DashboardLibraries = defineAsyncComponent(() => import('@/components/dashboard/DashboardLibraries.vue'));
const DashboardActivity = defineAsyncComponent(() => import('@/components/dashboard/DashboardActivity.vue'));
const DashboardUsers = defineAsyncComponent(() => import('@/components/dashboard/DashboardUsers.vue'));
const DashboardTasks = defineAsyncComponent(() => import('@/components/dashboard/DashboardTasks.vue'));

const activeComponent = computed(() => {
    switch (activeDashboardTab.value?.name) {
        case 'overview':
            return DashboardAnalytics;
        case 'libraries':
            return DashboardLibraries;
        case 'activity':
            return DashboardActivity;
        case 'users':
            return DashboardUsers;
        case 'tasks':
            return DashboardTasks;
        default:
            return null;
    }
});

onMounted(async () => {
    const screenSize = getScreenSize();

    if (screenSize === 'default' || screenSize === 'sm' || screenSize === 'md') return;

    cycleSideBar('dashboard', 'left-card', false);
});
</script>

<template>
    <LayoutBase>
        <template v-slot:content>
            <Suspense :timeout="0" :key="activeDashboardTab?.name">
                <template #default>
                    <Transition name="fade" mode="out-in" appear>
                        <section id="content-dashboard" class="page-height flex flex-col gap-4 text-sm">
                            <component :is="activeComponent" />
                        </section>
                    </Transition>
                </template>

                <template #fallback>
                    <Transition name="fade" mode="out-in">
                        <DashboardSkeleton class="page-height" />
                    </Transition>
                </template>
            </Suspense>
        </template>
        <template v-slot:leftSidebar>
            <DashboardSidebar />
        </template>
    </LayoutBase>
</template>

<style scoped lang="css">
.fade-enter-active,
.fade-leave-active {
    transition: opacity 150ms ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
