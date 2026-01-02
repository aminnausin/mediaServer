import type { SidebarTabItem } from '@/types/types';

import { computed, ref, watch } from 'vue';
import { useDashboardStore } from '@/stores/DashboardStore';
import { useAuthStore } from '@/stores/AuthStore';
import { storeToRefs } from 'pinia';
import { useAppStore } from '@/stores/AppStore';
import { useRoute } from 'vue-router';

import ProiconsLibrary from '~icons/proicons/library';
import ProiconsGraph from '~icons/proicons/graph';
import CircumServer from '~icons/circum/server';
import LucideUsers from '~icons/lucide/users';

export function useDashboardTabs() {
    const { stateTaskStats, stateTotalLibrariesSize, stateLibraryId, stateActiveSessions } = storeToRefs(useDashboardStore());

    const { userData } = storeToRefs(useAuthStore());
    const { pageTitle } = storeToRefs(useAppStore());

    const route = useRoute();

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
    return {
        dashboardTabs,
        activeDashboardTab: dashboardTab,
    };
}
