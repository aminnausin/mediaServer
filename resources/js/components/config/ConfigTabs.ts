import type { SidebarTabItem } from '@/types/types';

import { computed, ref, watch } from 'vue';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';
import { useAuth } from '@/composables/auth/useAuth';

import ProiconsSettings from '~icons/proicons/settings';
import ProiconsLayers from '~icons/proicons/layers';

export function useConfigTabs() {
    const { pageTitle } = storeToRefs(useAppStore());
    const { isAdmin } = useAuth();

    const route = useRoute();

    const configTab = ref<{ name: string; title?: string; icon?: any }>();
    const configTabs = computed<SidebarTabItem[]>(() => {
        return [
            {
                name: 'general',
                title: 'general',
                description: 'Server Features', // Server features
                disabled: !isAdmin.value,
                icon: ProiconsSettings,
            },
            {
                name: 'performance',
                title: 'performance',
                description: 'Resource Allocation', // Server features
                disabled: !isAdmin.value,
                icon: ProiconsLayers,
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
