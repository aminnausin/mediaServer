<script setup lang="ts">
import type { BreadCrumbItem } from '@/types/types';

import { BreadCrumbs } from '@/components/cedar-ui/breadcrumbs';
import { computed } from 'vue';

import ConfigServerPerformance from '@/components/config/performance/ConfigServerPerformance.vue';

import ProiconsSettings from '~icons/proicons/settings';

const breadCrumbs = computed(() => {
    const items: BreadCrumbItem[] = [
        {
            name: 'Config',
            url: '/config',
            icon: ProiconsSettings,
        },
        {
            name: 'Performance',
            url: '/config/performance',
        },
    ];

    return items;
});
</script>

<template>
    <section class="flex flex-wrap items-center justify-between gap-2">
        <BreadCrumbs :bread-crumbs="breadCrumbs" />
    </section>
    <ConfigServerPerformance />
</template>
