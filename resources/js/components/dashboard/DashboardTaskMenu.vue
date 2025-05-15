<script setup lang="ts">
import { handleStartTask } from '@/service/taskService';

import ButtonText from '@/components/inputs/ButtonText.vue';

import LucideFolderSearch from '~icons/lucide/folder-search';
import LucideFolderCheck from '~icons/lucide/folder-check';
import LucideFolderTree from '~icons/lucide/folder-tree';
import LucideFolderSync from '~icons/lucide/folder-sync';

const props = withDefaults(defineProps<{ showScanAll?: boolean }>(), { showScanAll: true });

const emit = defineEmits({ handleClose: () => {} });

const handleClose = (job: 'index' | 'sync' | 'verify' | 'scan' | 'verifyFolders') => {
    handleStartTask(job).then(() => emit('handleClose'));
};
</script>

<template>
    <div class="grid gap-4">
        <div class="space-y-2">
            <h4 class="font-medium leading-none">Start Server Task</h4>
        </div>

        <div class="grid gap-2 [&>*]:dark:bg-neutral-900 [&>*]:h-8 [&>*]:disabled:opacity-60">
            <ButtonText :title="'Scan for Folder Changes'" text="Index Files" @click="handleClose('index')">
                <template #icon> <LucideFolderSearch class="order-1 h-4 w-4" /></template>
            </ButtonText>
            <ButtonText :title="'Sync Folder With Database'" text="Sync Files" @click="handleClose('sync')">
                <template #icon> <LucideFolderSync class="order-1 h-4 w-4" /></template>
            </ButtonText>
            <ButtonText :title="'Scan for New Metadata'" text="Verify Metadata" @click="handleClose('verify')">
                <template #icon> <LucideFolderCheck class="order-1 h-4 w-4" /></template>
            </ButtonText>

            <ButtonText :title="'Scan for New Metadata'" text="Verify Folders" @click="handleClose('verifyFolders')">
                <template #icon> <LucideFolderCheck class="order-1 h-4 w-4" /></template>
            </ButtonText>

            <ButtonText v-if="showScanAll" class="dark:!bg-rose-700" title="Scan and Index All Files For Metadata" text="Scan All Files" @click="handleClose('scan')">
                <template #icon> <LucideFolderTree class="order-1 h-4 w-4" /></template>
            </ButtonText>
        </div>
    </div>
</template>
