<script setup lang="ts">
import type { BreadCrumbItem } from '@/types/types';

import { BreadCrumbs } from '@/components/cedar-ui/breadcrumbs';
import { useRouter } from 'vue-router';
import { computed } from 'vue';
import { useAuth } from '@/composables/auth/useAuth';

import SettingsServerPerformance from '@/components/settings/server/SettingsServerPerformance.vue';
import SettingsServerIndexing from '@/components/settings/server/SettingsServerIndexing.vue';
import SettingsServerStorage from '@/components/settings/server/SettingsServerStorage.vue';

import ProiconsSettings from '~icons/proicons/settings';

const { isAdmin } = useAuth();
const router = useRouter();

if (!isAdmin.value) {
    router.replace('/settings');
}

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
    <SettingsServerStorage />
    <SettingsServerPerformance />
</template>
