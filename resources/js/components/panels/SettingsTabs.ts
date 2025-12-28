import type { SidebarTabItem } from '@/types/types';

import { computed, ref, watch } from 'vue';
import { useAuthStore } from '@/stores/AuthStore';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';

import ProiconsSettings from '~icons/proicons/settings';
import LucideUser from '~icons/lucide/user';

export function useSettingsTabs() {
    const { userData } = storeToRefs(useAuthStore());
    const { pageTitle } = storeToRefs(useAppStore());

    const route = useRoute();
    const dev = true;

    const settingsTab = ref<{ name: string; title?: string; icon?: any }>();
    const settingsTabs = computed<SidebarTabItem[]>(() => {
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
                disabled: dev || !userData.value,
            },
            {
                name: 'server',
                title: 'Server',
                description: 'Server Settings', // Disable features
                disabled: dev || !userData.value || userData.value?.id !== 1,
            },
        ];
    });

    watch(
        () => route?.params?.tab,
        (URL_TAB) => {
            if (!URL_TAB) return;
            const defaultTab = settingsTabs.value.find((tab) => tab.title === URL_TAB || tab.name === URL_TAB) ?? settingsTabs.value[0];

            pageTitle.value = defaultTab.title ?? defaultTab.name;
            settingsTab.value = defaultTab;
        },
        { immediate: true },
    );

    watch(
        () => settingsTab.value,
        () => {
            if (!settingsTab.value) return;
            pageTitle.value = settingsTab.value.title ?? settingsTab.value.name;
        },
    );
    return {
        settingsTabs,
        activeSettingsTab: settingsTab,
    };
}
