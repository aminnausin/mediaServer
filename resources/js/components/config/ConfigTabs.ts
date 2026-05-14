import { createTabStore } from '@/stores/TabStore';
import { storeToRefs } from 'pinia';
import { useAuth } from '@/composables/auth/useAuth';

import ProiconsSettings from '~icons/proicons/settings';
import ProiconsLayers from '~icons/proicons/layers';

export function useConfigTabs() {
    const store = createTabStore('config', () => {
        const { isAdmin } = useAuth();
        return [
            {
                name: 'general',
                title: 'general',
                description: 'Server Features',
                disabled: !isAdmin.value,
                icon: ProiconsSettings,
            },
            {
                name: 'performance',
                title: 'performance',
                description: 'Resource Allocation',
                disabled: !isAdmin.value,
                icon: ProiconsLayers,
            },
        ];
    })();

    const { tabs, activeTab } = storeToRefs(store);

    return { configTabs: tabs, activeConfigTab: activeTab, setTab: store.setTab };
}
