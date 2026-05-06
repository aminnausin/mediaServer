<script setup lang="ts">
import { useSettingsTabs } from '@/components/panels/SettingsTabs';
import { useConfigTabs } from '@/components/panels/ConfigTabs';
import { useRoute } from 'vue-router';
import { computed } from 'vue';

import DashboardSidebarCard from '@/components/cards/sidebar/DashboardSidebarCard.vue';
import AppManifestCard from '@/components/cards/data/AppManifestCard.vue';
import SidebarHeader from '@/components/headers/SidebarHeader.vue';

const { configTabs, activeConfigTab } = useConfigTabs();
const { settingsTabs, activeSettingsTab } = useSettingsTabs();

const route = useRoute();

const activeRoute = computed(() => (route.name === 'config' ? 'config' : 'settings'));

const tabs = computed(() => (route.name === 'config' ? configTabs.value : settingsTabs.value));

const activeTab = computed(() => (route.name === 'config' ? activeConfigTab.value : activeSettingsTab.value));
</script>

<template>
    <SidebarHeader />
    <div class="full-height-sidebar flex h-full flex-1 flex-col gap-2">
        <DashboardSidebarCard
            v-for="(tab, index) in tabs.filter((tab) => !tab.disabled)"
            :key="index"
            :to="`/${activeRoute}/${tab.name}`"
            :disabled="tab.disabled"
            :is-active="activeTab?.name === tab.name"
            @click="activeTab = tab"
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
