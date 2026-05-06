<script setup lang="ts">
import type { BreadCrumbItem } from '@/types/types';

import { BreadCrumbs } from '@/components/cedar-ui/breadcrumbs';
import { useRouter } from 'vue-router';
import { computed } from 'vue';
import { useAuth } from '@/composables/auth/useAuth';

import ConfigServerPerformance from '@/components/config/server/ConfigServerPerformance.vue';
import ConfigServerIndexing from '@/components/config/server/ConfigServerIndexing.vue';
import ConfigServerStorage from '@/components/config/server/ConfigServerStorage.vue';

import ProiconsSettings from '~icons/proicons/settings';

const { isAdmin } = useAuth();
const router = useRouter();

if (!isAdmin.value) {
    router.replace('/settings');
}

const breadCrumbs = computed(() => {
    const items: BreadCrumbItem[] = [
        {
            name: 'Config',
            url: '/config',
            icon: ProiconsSettings,
        },
        {
            name: 'General Config',
            url: '/config/general',
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
    <ConfigServerIndexing />
    <ConfigServerStorage />
    <ConfigServerPerformance />
</template>
