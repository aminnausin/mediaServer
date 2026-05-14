import { createTabStore } from '@/stores/TabStore';
import { storeToRefs } from 'pinia';
import { useAuth } from '@/composables/auth/useAuth';

import ProiconsSettings from '~icons/proicons/settings';
import LucideUser from '~icons/lucide/user';

export function useSettingsTabs() {
    const { userData } = useAuth();

    const store = createTabStore('settings', () => {
        return [
            {
                name: 'preferences',
                title: 'Preferences',
                description: 'Website Preferences', // Remember Volume, Ambient Mode, Playback Heatmap, Opt out of things?
                icon: ProiconsSettings,
            },
            {
                name: 'account',
                title: 'Account',
                description: 'Account Settings', // Email Password Sessions
                disabled: !userData.value,
                icon: LucideUser,
            },
            {
                name: 'profile',
                title: 'Profile',
                description: 'Profile Settings', // Visibility, Avatar, Banner
                disabled: true,
            },
        ];
    })();

    const { tabs, activeTab } = storeToRefs(store);

    return { settingsTabs: tabs, activeSettingsTab: activeTab, setTab: store.setTab };
}
