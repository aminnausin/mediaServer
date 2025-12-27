<script setup lang="ts">
import { ButtonText } from '@/components/cedar-ui/button';

import ProiconsDelete from '~icons/proicons/delete';
import ProiconsLock from '~icons/proicons/lock';
import CircumEdit from '~icons/circum/edit';

const props = withDefaults(defineProps<{ handleClosePopover?: () => void }>(), {});
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
            <ButtonText title="Manage Metadata Settings" disabled>
                <p class="flex-1 text-start">Manage Metadata</p>
                <template #icon> <ProiconsLock class="size-4" /></template>
            </ButtonText>
            <ButtonText class="text-danger dark:text-foreground-0 dark:bg-danger-3! dark:hocus:bg-danger!" title="Remove From Server" disabled>
                <p class="flex-1 text-start">Remove Folder</p>
                <template #icon> <ProiconsDelete class="size-4" /></template>
            </ButtonText>
        </div>
    </div>
</template>
