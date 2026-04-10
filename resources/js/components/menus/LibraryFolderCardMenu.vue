<script setup lang="ts">
import type { FolderResource } from '@/contracts/media';

import { ButtonText } from '@/components/cedar-ui/button';
import { FLAGS } from '@/config/featureFlags';

import TablerDownloadOff from '@/components/icons/TablerDownloadOff.vue';
import TablerDownload from '@/components/icons/TablerDownload.vue';

import ProiconsDelete from '~icons/proicons/delete';
import CircumEdit from '~icons/circum/edit';

const props = withDefaults(
    defineProps<{
        handleClosePopover?: () => void;
        data: FolderResource;
        processing: boolean;
        libraryDownloadsEnabled: boolean;
        handleToggleDownloads: (id: number, currentValue: boolean) => Promise<void>;
    }>(),
    {},
);
</script>

<template>
    <div class="space-y-4">
        <div class="space-y-2">
            <h4 class="leading-none font-medium">Manage Folder</h4>
            <p class="text-foreground-1 text-sm">Set Folder Properties.</p>
        </div>

        <div class="flex flex-col gap-2 dark:*:bg-neutral-900">
            <ButtonText
                title="Edit Folder"
                @click.stop.prevent="
                    () => {
                        if (handleClosePopover) handleClosePopover();
                        $emit('clickAction');
                    }
                "
            >
                <p class="flex-1 text-start">Edit Folder</p>
                <template #icon> <CircumEdit class="size-4" /></template>
            </ButtonText>

            <ButtonText
                v-if="libraryDownloadsEnabled && data.series"
                :title="'Toggle Downloads'"
                @click="handleToggleDownloads(data.series.id, data.series.downloads_enabled ?? false)"
                :disabled="processing"
            >
                <p class="flex-1 text-start">{{ data.series?.downloads_enabled ? 'Disable Downloads' : 'Enable Downloads' }}</p>
                <template #icon> <TablerDownload v-if="data.series?.downloads_enabled" class="size-4" /> <TablerDownloadOff v-else class="size-4" /></template>
            </ButtonText>

            <ButtonText v-if="FLAGS.USE_REMOVABLE_FOLDERS" class="text-danger dark:text-foreground-0 dark:bg-danger-3! dark:hocus:bg-danger!" title="Remove From Server" disabled>
                <p class="flex-1 text-start">Remove Folder</p>
                <template #icon> <ProiconsDelete class="size-4" /></template>
            </ButtonText>
        </div>
    </div>
</template>
