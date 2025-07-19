<script setup lang="ts">
import type { BreadCrumbItem } from '@/types/types';

import { computed } from 'vue';

import SettingsPlayerPreferences from '@/components/settings/preferences/SettingsPlayerPreferences.vue';
import BreadCrumbs from '@/components/pinesUI/BreadCrumbs.vue';

import ProiconsSettings from '~icons/proicons/settings';

const breadCrumbs = computed(() => {
    const items: BreadCrumbItem[] = [
        {
            name: 'Settings',
            url: '/settings/preferences',
            icon: ProiconsSettings,
        },
    ];

    return items;
});
</script>

<template>
    <section class="flex items-center gap-2 justify-between flex-wrap">
        <BreadCrumbs :bread-crumbs="breadCrumbs" />
    </section>
    <SettingsPlayerPreferences />
</template>
