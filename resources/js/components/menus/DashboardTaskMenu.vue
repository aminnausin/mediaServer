<script setup lang="ts">
import { handleStartTask } from '@/service/taskService';

import { ButtonText } from '@/components/cedar-ui/button';

import LucideFolderSearch from '~icons/lucide/folder-search';
import LucideFolderCheck from '~icons/lucide/folder-check';
import LucideFolderTree from '~icons/lucide/folder-tree';
import LucideFolderSync from '~icons/lucide/folder-sync';

withDefaults(defineProps<{ showScanAll?: boolean }>(), { showScanAll: true });

const emit = defineEmits({
    handleClose: () => true,
});

const handleClose = (job: 'index' | 'sync' | 'verify' | 'scan' | 'verifyFolders') => {
    handleStartTask(job).then(() => emit('handleClose'));
};
</script>

<template>
    <section class="grid gap-4">
        <h4 class="leading-none font-medium">Start Server Task</h4>

        <div class="grid gap-2 *:h-8 dark:*:bg-neutral-900">
            <ButtonText title="Scan for Folder Changes" text="Index Files" @click="handleClose('index')">
                <template #icon> <LucideFolderSearch class="size-4" /></template>
            </ButtonText>
            <ButtonText title="Sync Folder With Database" text="Sync Files" @click="handleClose('sync')">
                <template #icon> <LucideFolderSync class="size-4" /></template>
            </ButtonText>
            <ButtonText title="Scan for New Metadata" text="Verify Metadata" @click="handleClose('verify')">
                <template #icon> <LucideFolderCheck class="size-4" /></template>
            </ButtonText>

            <ButtonText title="Scan for New Metadata" text="Verify Folders" @click="handleClose('verifyFolders')">
                <template #icon> <LucideFolderCheck class="size-4" /></template>
            </ButtonText>

            <ButtonText
                v-if="showScanAll"
                class="text-danger dark:text-foreground-0 dark:bg-danger-3! dark:hocus:bg-danger!"
                title="Scan and Index All Files For Metadata"
                text="Scan All Files"
                @click="handleClose('scan')"
            >
                <template #icon> <LucideFolderTree class="size-4" /></template>
            </ButtonText>
        </div>
    </section>
</template>
