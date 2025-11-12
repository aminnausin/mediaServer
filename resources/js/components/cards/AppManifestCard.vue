<script setup lang="ts">
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';

import SidebarCard from '@/components/cards/SidebarCard.vue';

import ProiconsGithub from '~icons/proicons/github';

const { appManifest } = storeToRefs(useAppStore());
</script>

<template>
    <SidebarCard
        :to="`${appManifest?.commit ? `https://github.com/aminnausin/mediaServer/commit/${appManifest?.commit}` : ''}`"
        target="_blank"
        :class="[
            'items-center justify-between',
            'hover:bg-primary-800 overflow-hidden bg-white capitalize',
            'ring-purple-600 ring-inset hover:ring-2 hover:ring-purple-600/50',
            'aria-disabled:cursor-not-allowed aria-disabled:opacity-60 aria-disabled:hover:ring-neutral-200 dark:aria-disabled:hover:ring-neutral-700',
        ]"
        :aria-disabled="false"
    >
        <template #header>
            <h3 class="text-gray-900 dark:text-white" :title="'Source Code'">MediaServer</h3>
            <ProiconsGithub class="ml-auto h-6 w-6" />
        </template>
        <template #body>
            <h4 title="App Version" class="w-full flex-1 truncate text-wrap sm:text-nowrap">V{{ appManifest.version ?? '0.1.15b' }}</h4>
            <h4 v-if="appManifest.commit" title="Information" class="w-fit truncate text-nowrap sm:text-right">#{{ appManifest.commit }}</h4>
        </template>
    </SidebarCard>
</template>
