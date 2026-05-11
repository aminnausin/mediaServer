<script setup lang="ts">
import type { BreadCrumbItem } from '@/types/types';

import { BreadCrumbs } from '@/components/cedar-ui/breadcrumbs';
import { computed } from 'vue';

import ConfigServerFileTypes from '@/components/config/server/ConfigServerFileTypes.vue';
import ConfigServerIndexing from '@/components/config/server/ConfigServerIndexing.vue';
import ConfigServerStorage from '@/components/config/server/ConfigServerStorage.vue';

import ProiconsSettings from '~icons/proicons/settings';

const breadCrumbs = computed(() => {
    const items: BreadCrumbItem[] = [
        {
            name: 'Config',
            url: '/config',
            icon: ProiconsSettings,
        },
        {
            name: 'General',
            url: '/config/general',
        },
    ];

    return items;
});
</script>

<template>
    <section class="flex flex-wrap items-center justify-between gap-2">
        <BreadCrumbs :bread-crumbs="breadCrumbs" />
    </section>
    <ConfigServerIndexing />
    <ConfigServerStorage />
    <ConfigServerFileTypes />
</template>
