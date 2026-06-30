import type { SidebarTabItem } from '@/types/types';

import { useContentStore } from '@/stores/ContentStore';
import { createTabStore } from '@/stores/TabStore';
import { storeToRefs } from 'pinia';

export function useFolderTabs() {
    const { stateFolder } = storeToRefs(useContentStore());

    const tabsStore = createTabStore(
        `folder-info`,
        () => [{ name: 'overview' }, { name: 'files' }, { name: 'images' }, { name: 'metadata' }, { name: 'stats' }],
        (tab: SidebarTabItem) => `${stateFolder.value.title} ${tab.name}`,
    )();

    const { activeTab: activeFolderTab, tabs } = storeToRefs(tabsStore);

    return { tabs, activeFolderTab, setTab: tabsStore.setTab };
}
