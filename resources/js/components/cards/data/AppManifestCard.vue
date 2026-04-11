<script setup lang="ts">
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';

import DashboardSidebarCard from '@/components/cards/sidebar/DashboardSidebarCard.vue';

import ProiconsGithub from '~icons/proicons/github';

const { appManifest } = storeToRefs(useAppStore());
</script>

<template>
    <DashboardSidebarCard target="_blank" :href="`${appManifest?.commit ? `https://github.com/aminnausin/mediaServer/commit/${appManifest.commit}` : ''}`" :disabled="false">
        <template #header>
            <h3 :title="'Source Code'" class="truncate">MediaServer</h3>
            <ProiconsGithub class="ml-auto size-5 shrink-0" />
        </template>
        <template #body>
            <h4 title="App Version" class="w-full flex-1 text-nowrap sm:truncate">V{{ appManifest.version ?? '0.1.15b' }}</h4>
            <h4 v-if="appManifest.commit" title="Latest Commit" class="w-fit truncate text-nowrap">#{{ appManifest.commit }}</h4>
        </template>
    </DashboardSidebarCard>
</template>
