<script setup lang="ts">
import type { BreadCrumbItem } from '@/types/types';

import { BreadCrumbs } from '@/components/cedar-ui/breadcrumbs';
import { computed } from 'vue';

import SettingsServerPerformance from '@/components/settings/server/SettingsServerPerformance.vue';
import SettingsServerIndexing from '@/components/settings/server/SettingsServerIndexing.vue';
import SettingsServerPaths from '@/components/settings/server/SettingsServerPaths.vue';

import ProiconsSettings from '~icons/proicons/settings';

const breadCrumbs = computed(() => {
    const items: BreadCrumbItem[] = [
        {
            name: 'Settings',
            url: '/settings/preferences',
            icon: ProiconsSettings,
        },
        {
            name: 'Server Config',
            url: '/settings/server',
            icon: ProiconsSettings,
        },
    ];

    return items;
});
</script>

<template>
    <section class="flex flex-wrap items-center justify-between gap-2">
        <BreadCrumbs :bread-crumbs="breadCrumbs" />
    </section>
    <SettingsServerIndexing />
    <SettingsServerPaths />
    <SettingsServerPerformance />
</template>
