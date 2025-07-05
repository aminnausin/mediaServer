<script setup lang="ts">
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import SidebarCard from '@/components/cards/SidebarCard.vue';

const { appManifest } = storeToRefs(useAppStore());
</script>

<template>
    <SidebarCard
        :to="`${appManifest?.commit ? `https://github.com/aminnausin/mediaServer/commit/${appManifest?.commit}` : ''}`"
        target="_blank"
        :class="[
            'items-center justify-between',
            'capitalize overflow-hidden bg-white hover:bg-primary-800',
            'ring-inset ring-purple-600 hover:ring-purple-600/50 hover:ring-[0.125rem]',
            'aria-disabled:cursor-not-allowed aria-disabled:hover:ring-neutral-200 aria-disabled:hover:dark:ring-neutral-700  aria-disabled:opacity-60',
        ]"
        :aria-disabled="false"
    >
        <template #header>
            <h3 class="text-gray-900 dark:text-white" :title="'Source Code'">MediaServer</h3>
            <ProiconsGithub class="ml-auto w-6 h-6" />
        </template>
        <template #body>
            <h4 title="App Version" class="w-full text-wrap truncate sm:text-nowrap flex-1">V{{ appManifest.version ?? '0.1.15b' }}</h4>
            <h4 v-if="appManifest.commit" title="Information" class="truncate text-nowrap sm:text-right w-fit">#{{ appManifest.commit }}</h4>
        </template>
    </SidebarCard>
</template>
