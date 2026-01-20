<script setup lang="ts">
import { useSettingsTabs } from '@/components/panels/SettingsTabs';

import DashboardSidebarCard from '@/components/cards/sidebar/DashboardSidebarCard.vue';
import AppManifestCard from '@/components/cards/data/AppManifestCard.vue';
import SidebarHeader from '@/components/headers/SidebarHeader.vue';

import CircumGrid31 from '~icons/circum/grid-3-1';

const { settingsTabs, activeSettingsTab } = useSettingsTabs();
</script>

<template>
    <SidebarHeader />
    <div class="flex flex-1 flex-col gap-2">
        <DashboardSidebarCard
            v-for="(tab, index) in settingsTabs.filter((tab) => !tab.disabled)"
            :key="index"
            :to="`/settings/${tab.name}`"
            :disabled="tab.disabled"
            :is-active="activeSettingsTab?.name === tab.name"
            @click="activeSettingsTab = tab"
        >
            <template #header>
                <h3 class="line-clamp-1 w-full flex-1" :title="tab.title ?? tab.name">{{ tab.title ?? tab.name }}</h3>
                <component v-if="tab.icon" :is="tab.icon" class="ml-auto size-6" />
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
        <DashboardSidebarCard :to="`/dashboard`">
            <template #header>
                <h3 :title="'Dashboard'">Dashboard</h3>
                <CircumGrid31 class="ml-auto size-6" />
            </template>
            <template #body>
                <h4 title="App Dashboard" class="w-full flex-1 truncate text-wrap sm:text-nowrap">Server Analytics</h4>
            </template>
        </DashboardSidebarCard>
        <AppManifestCard />
    </div>
</template>
