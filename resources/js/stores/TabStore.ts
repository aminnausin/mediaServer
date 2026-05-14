import type { SidebarTabItem } from '@/types/types';

import { ref, computed } from 'vue';
import { useAppStore } from '@/stores/AppStore';
import { defineStore } from 'pinia';

export function createTabStore(id: string, getTabs: () => SidebarTabItem[]) {
    return defineStore(`tabs-${id}`, () => {
        const tabs = computed(getTabs);
        const activeTab = ref<SidebarTabItem | undefined>();

        const setTab = (tabParam: string | string[]) => {
            const tab = Array.isArray(tabParam) ? tabParam[0] : tabParam;
            const match = tabs.value.find((t) => t.name === tab || t.title === tab);

            activeTab.value = match ?? tabs.value[0];

            if (activeTab.value) useAppStore().pageTitle = activeTab.value.title ?? activeTab.value.name;
        };

        return { tabs, activeTab, setTab };
    });
}
