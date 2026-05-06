import type { SidebarTabItem } from '@/types/types';

import { computed, ref, watch } from 'vue';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';
import { useAuth } from '@/composables/auth/useAuth';

import ProiconsSettings from '~icons/proicons/settings';

export function useConfigTabs() {
    const { isAdmin, userData } = useAuth();
    const { pageTitle } = storeToRefs(useAppStore());

    const route = useRoute();

    const configTab = ref<{ name: string; title?: string; icon?: any }>();
    const configTabs = computed<SidebarTabItem[]>(() => {
        return [
            {
                name: 'general',
                title: 'general',
                description: 'General Server Configuration', // Server features
                disabled: !isAdmin.value,
                icon: ProiconsSettings,
            },
        ];
    });

    watch(
        () => route?.params?.tab,
        (URL_TAB) => {
            if (!URL_TAB) return;
            const defaultTab = configTabs.value.find((tab) => tab.title === URL_TAB || tab.name === URL_TAB) ?? configTabs.value[0];

            pageTitle.value = defaultTab.title ?? defaultTab.name;
            configTab.value = defaultTab;
        },
        { immediate: true },
    );

    watch(
        () => configTab.value,
        () => {
            if (!configTab.value) return;
            pageTitle.value = configTab.value.title ?? configTab.value.name;
        },
    );
    return {
        configTabs,
        activeConfigTab: configTab,
    };
}
