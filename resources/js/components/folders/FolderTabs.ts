import { createTabStore } from '@/stores/TabStore';
import { storeToRefs } from 'pinia';

export function useFolderTabs() {
    const tabsStore = createTabStore(`folder-info`, () => [{ name: 'overview' }, { name: 'files' }, { name: 'images' }, { name: 'metadata' }, { name: 'stats' }])();

    const { activeTab: activeFolderTab, tabs } = storeToRefs(tabsStore);

    return { tabs, activeFolderTab, setTab: tabsStore.setTab };
}
