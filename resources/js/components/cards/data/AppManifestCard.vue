<script setup lang="ts">
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { toast } from '@aminnausin/cedar-ui';

import DashboardSidebarCard from '@/components/cards/sidebar/DashboardSidebarCard.vue';

import ProiconsGithub from '~icons/proicons/github';

const { appManifest } = storeToRefs(useAppStore());

const copyToClipboard = async () => {
    const version = appManifest.value.version;
    if (!version) {
        toast.error('Error', { description: 'This version is a placeholder.' });
        return;
    }
    try {
        await navigator.clipboard.writeText(version);
        toast.success('Success', { description: `Copied version ${version}` });
    } catch (error) {
        toast.error('Error', { description: `Unable to copy version ${version}` });
        console.error(error);
    }
};
</script>

<template>
    <DashboardSidebarCard class="pointer-default">
        <template #header>
            <a title="GitHub" class="hover:text-primary dark:hover:text-primary-muted contents" href="https://github.com/aminnausin/mediaServer/" target="_blank">
                <h3 :title="'Source Code'" class="truncate">MediaServer</h3>
                <ProiconsGithub class="ml-auto size-5 shrink-0" />
            </a>
        </template>
        <template #body>
            <h4 title="App Version - Click to copy" class="hover:text-primary dark:hover:text-primary-muted w-full flex-1 text-nowrap sm:truncate" @click.prevent="copyToClipboard">
                V{{ appManifest.version ?? '0.1.15b' }}
            </h4>
            <a v-if="appManifest.commit" target="_blank" :href="`https://github.com/aminnausin/mediaServer/commit/${appManifest.commit}`">
                <h4 title="Latest Commit" class="hover:text-primary dark:hover:text-primary-muted w-fit truncate text-nowrap">#{{ appManifest.commit }}</h4>
            </a>
        </template>
    </DashboardSidebarCard>
</template>
