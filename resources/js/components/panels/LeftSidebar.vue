<script setup lang="ts">
import type { SidebarTabItem } from '@/types/types';

import { useDashboardTabs } from '@/components/dashboard/DashboardTabs';
import { useSettingsTabs } from '@/components/settings/SettingsTabs';
import { useConfigTabs } from '@/components/config/ConfigTabs';
import { useRoute } from 'vue-router';
import { computed } from 'vue';

import DashboardSidebarCard from '@/components/cards/sidebar/DashboardSidebarCard.vue';
import AppManifestCard from '@/components/cards/data/AppManifestCard.vue';
import SidebarHeader from '@/components/headers/SidebarHeader.vue';

const { configTabs, activeConfigTab } = useConfigTabs();
const { settingsTabs, activeSettingsTab } = useSettingsTabs();
const { dashboardTabs, activeDashboardTab } = useDashboardTabs();

const route = useRoute();

const routeMap = computed<{ [key: string]: { tabs: SidebarTabItem[]; activeTab: any; base: string } }>(() => ({
    config: { tabs: configTabs.value, activeTab: activeConfigTab, base: 'config' },
    settings: { tabs: settingsTabs.value, activeTab: activeSettingsTab, base: 'settings' },
    preferences: { tabs: settingsTabs.value, activeTab: activeSettingsTab, base: 'settings' },
    dashboard: { tabs: dashboardTabs.value, activeTab: activeDashboardTab, base: 'dashboard' },
}));

const currentMap = computed(() => routeMap.value[route.name as string] ?? routeMap.value.dashboard);
</script>

<template>
    <SidebarHeader />
    <div class="full-height-sidebar flex h-full flex-1 flex-col gap-2">
        <DashboardSidebarCard
            v-for="(tab, index) in currentMap.tabs.filter((tab) => !tab.disabled)"
            :key="index"
            :to="`/${currentMap.base}/${tab.name}`"
            :disabled="tab.disabled"
            :is-active="currentMap.activeTab?.name === tab.name"
            @click="currentMap.activeTab = tab"
        >
            <template #header>
                <h3 class="w-full flex-1 truncate" :title="tab.title ?? tab.name">{{ tab.title ?? tab.name }}</h3>
                <component v-if="tab.icon" :is="tab.icon" class="ml-auto size-5" />
            </template>
            <template #body>
                <h4 v-if="tab.description" title="Description" class="w-full flex-1 truncate text-wrap sm:text-nowrap">
                    {{ tab.description }}
                </h4>
                <h4 v-if="tab.info" title="Information" class="w-fit truncate text-nowrap sm:text-right">
                    {{ tab.info.value }}
                </h4>
            </template>
        </DashboardSidebarCard>
        <AppManifestCard :class="'mt-auto'" />
    </div>
</template>
