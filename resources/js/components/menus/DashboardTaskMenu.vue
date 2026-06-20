<script setup lang="ts">
import { handleStartTask } from '@/service/taskService';
import { ContextMenuItem } from '../cedar-ui/context-menu';

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
    <div class="*:h-8 *:rounded-md">
        <ContextMenuItem title="Scan for Folder Changes" text="Index Files" @click="handleClose('index')">
            <template #icon> <LucideFolderSearch class="size-4" /></template>
        </ContextMenuItem>
        <ContextMenuItem title="Sync Folder With Database" text="Sync Files" @click="handleClose('sync')">
            <template #icon> <LucideFolderSync class="size-4" /></template>
        </ContextMenuItem>
        <ContextMenuItem title="Scan for New Metadata" text="Verify Metadata" @click="handleClose('verify')">
            <template #icon> <LucideFolderCheck class="size-4" /></template>
        </ContextMenuItem>

        <ContextMenuItem title="Scan for New Metadata" text="Verify Folders" @click="handleClose('verifyFolders')">
            <template #icon> <LucideFolderCheck class="size-4" /></template>
        </ContextMenuItem>

        <ContextMenuItem
            v-if="showScanAll"
            class="text-danger dark:text-danger-0 dark:bg-danger-3/10 dark:hocus:bg-danger-3 dark:hocus:text-foreground-0 transition-[color,background-color]"
            title="Scan and Index All Files For Metadata"
            text="Scan All Files"
            @click="handleClose('scan')"
        >
            <template #icon> <LucideFolderTree class="size-4" /></template>
        </ContextMenuItem>
    </div>
</template>
