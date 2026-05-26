import { useDashboardStore } from '@/stores/DashboardStore';
import { createTabStore } from '@/stores/TabStore';
import { storeToRefs } from 'pinia';
import { useAuth } from '@/composables/auth/useAuth';
import { watch } from 'vue';

import ProiconsLibrary from '~icons/proicons/library';
import ProiconsGraph from '~icons/proicons/graph';
import CircumServer from '~icons/circum/server';
import LucideUsers from '~icons/lucide/users';

export function useDashboardTabs() {
    const { stateLibraryId } = storeToRefs(useDashboardStore());

    const store = createTabStore('dashboard', () => {
        const { stateTaskStats, stateTotalLibrariesSize, stateActiveSessions } = storeToRefs(useDashboardStore());

        const { userData } = useAuth();
        return [
            {
                name: 'overview',
                title: 'Analytics',
                description: 'Website Overview',
                icon: ProiconsGraph,
            },
            {
                name: 'libraries',
                title: 'Libraries',
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
                title: 'Users',
                info: { value: `Logged In: ${stateActiveSessions?.value ?? '?'}` },
                icon: LucideUsers,
            },
            {
                name: 'tasks',
                title: 'Tasks',
                info: { value: `Currently Running: ${stateTaskStats.value?.count_running ?? '?'}` },
                icon: CircumServer,
                disabled: userData.value?.id !== 1,
            },
        ];
    })();

    const { tabs, activeTab } = storeToRefs(store);

    watch(
        () => activeTab.value,
        (value) => {
            if (value?.name !== 'libraries') stateLibraryId.value = -1;
        },
    );

    return { dashboardTabs: tabs, activeDashboardTab: activeTab, setTab: store.setTab };
}
