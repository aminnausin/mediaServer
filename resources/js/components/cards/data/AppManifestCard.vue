<script setup lang="ts">
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';

import DashboardSidebarCard from '@/components/cards/sidebar/DashboardSidebarCard.vue';

import ProiconsGithub from '~icons/proicons/github';

const { appManifest } = storeToRefs(useAppStore());
</script>

<template>
    <DashboardSidebarCard target="_blank" :to="`${appManifest?.commit ? `https://github.com/aminnausin/mediaServer/commit/${appManifest.commit}` : ''}`" :disabled="false">
        <template #header>
            <h3 :title="'Source Code'">MediaServer</h3>
            <ProiconsGithub class="ml-auto h-6 w-6" />
        </template>
        <template #body>
            <h4 title="App Version" class="w-full flex-1 truncate text-wrap sm:text-nowrap">V{{ appManifest.version ?? '0.1.15b' }}</h4>
            <h4 v-if="appManifest.commit" title="Latest Commit" class="w-fit truncate text-nowrap sm:text-right">#{{ appManifest.commit }}</h4>
        </template>
    </DashboardSidebarCard>
</template>
