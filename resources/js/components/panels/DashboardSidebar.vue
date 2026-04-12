<script setup lang="ts">
import { useScrollbarDetection } from '@/composables/design/useScrollbarDetection';
import { useDashboardTabs } from '@/components/panels/DashboardTabs';
import { useTemplateRef } from 'vue';
import { FLAGS } from '@/config/featureFlags';

import DashboardSidebarCard from '@/components/cards/sidebar/DashboardSidebarCard.vue';
import AppManifestCard from '@/components/cards/data/AppManifestCard.vue';
import SidebarHeader from '@/components/headers/SidebarHeader.vue';

import ProiconsSettings from '~icons/proicons/settings';

const { dashboardTabs, activeDashboardTab } = useDashboardTabs();

const scrollContainer = useTemplateRef('scroll-container');
const { hasScrollbar } = useScrollbarDetection(scrollContainer);
</script>

<template>
    <SidebarHeader />

    <div
        :class="['full-height-sidebar clamped-sidebar scrollbar-minimal transition-padding flex flex-1 flex-col gap-2', { 'overflow-auto p-0.5 pe-1': hasScrollbar }]"
        ref="scroll-container"
    >
        <DashboardSidebarCard
            v-for="(tab, index) in dashboardTabs.filter((tab) => !tab.disabled)"
            :key="index"
            :to="`/dashboard/${tab.name}`"
            :disabled="tab.disabled"
            :is-active="activeDashboardTab?.name === tab.name"
            @click="activeDashboardTab = tab"
        >
            <template #header>
                <h3 class="w-full flex-1 truncate" :title="tab.title ?? tab.name">{{ tab.title ?? tab.name }}</h3>
                <component v-if="tab.icon" :is="tab.icon" class="ml-auto size-5" />
            </template>
            <template #body>
                <h4 v-if="tab.description" title="Description" class="w-full flex-1 truncate text-wrap sm:text-nowrap">
                    {{ tab.description }}
                </h4>
                <h4 v-if="tab.info" title="Information" class="w-full truncate text-nowrap sm:w-fit">
                    {{ tab.info.value }}
                </h4>
            </template>
        </DashboardSidebarCard>

        <DashboardSidebarCard v-if="FLAGS.USE_NAV_IN_SIDEBAR" :to="`/settings`">
            <template #header>
                <h3 :title="'Settings'">Settings</h3>
                <ProiconsSettings class="ml-auto size-6" />
            </template>
            <template #body>
                <h4 title="Description" class="w-full flex-1 truncate text-wrap sm:text-nowrap">Configurable Options</h4>
            </template>
        </DashboardSidebarCard>
        <AppManifestCard class="mt-auto" />
    </div>
</template>
<style lang="css" scoped>
.transition-padding {
    transition-property: padding;
    transition-timing-function: var(--tw-ease, var(--default-transition-timing-function) /* cubic-bezier(0.4, 0, 0.2, 1) */);
    transition-duration: var(--tw-duration, var(--default-transition-duration) /* 150ms */);
}
</style>
