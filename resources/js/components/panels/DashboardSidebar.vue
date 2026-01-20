<script setup lang="ts">
import { useDashboardTabs } from '@/components/panels/DashboardTabs';

import DashboardSidebarCard from '@/components/cards/sidebar/DashboardSidebarCard.vue';
import AppManifestCard from '@/components/cards/data/AppManifestCard.vue';
import SidebarHeader from '@/components/headers/SidebarHeader.vue';

import ProiconsSettings from '~icons/proicons/settings';

const { dashboardTabs, activeDashboardTab } = useDashboardTabs();
</script>

<template>
    <SidebarHeader />

    <div class="flex flex-1 flex-col gap-2">
        <DashboardSidebarCard
            v-for="(tab, index) in dashboardTabs.filter((tab) => !tab.disabled)"
            :key="index"
            :to="tab.disabled ? '' : `/dashboard/${tab.name}`"
            :is-active="activeDashboardTab?.name === tab.name"
            :aria-disabled="tab.disabled"
            @click="activeDashboardTab = tab"
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

        <DashboardSidebarCard :to="`/settings`" :aria-disabled="false">
            <template #header>
                <h3 :title="'Settings'">Settings</h3>
                <ProiconsSettings class="ml-auto size-6" />
            </template>
            <template #body>
                <h4 title="Description" class="w-full flex-1 truncate text-wrap sm:text-nowrap">Configurable Options</h4>
            </template>
        </DashboardSidebarCard>
        <AppManifestCard />
    </div>
</template>
