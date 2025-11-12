<script setup lang="ts">
import ButtonText from '@/components/inputs/ButtonText.vue';

import ProiconsDelete from '~icons/proicons/delete';
import ProiconsLock from '~icons/proicons/lock';
import CircumEdit from '~icons/circum/edit';

const props = withDefaults(defineProps<{ handleClosePopover?: () => void }>(), {});
</script>

<template>
    <div class="space-y-4">
        <div class="space-y-2">
            <h4 class="font-medium leading-none">Manage Folder</h4>
            <p class="text-sm text-muted-foreground">Set Folder Properties.</p>
        </div>

        <div class="*:w-full flex flex-col gap-2 dark:*:bg-neutral-900 *:h-8 disabled:*:opacity-60">
            <ButtonText
                class="h-8 dark:bg-neutral-950! disabled:opacity-60"
                :title="'Edit Folder'"
                @click.stop.prevent="
                    () => {
                        if (handleClosePopover) handleClosePopover();
                        $emit('clickAction');
                    }
                "
            >
                <template #text> <p class="flex-1 text-start">Edit Folder</p></template>
                <template #icon> <CircumEdit class="h-4 w-4" /></template>
            </ButtonText>
            <ButtonText :title="'Manage Metadata Settings'" disabled>
                <template #text> <p class="flex-1 text-start">Manage Metadata</p> </template>
                <template #icon> <ProiconsLock class="h-4 w-4" /></template>
            </ButtonText>
            <ButtonText class="text-rose-600 dark:bg-rose-700!" :title="'Remove From Server'" disabled>
                <template #text> <p class="flex-1 text-start">Remove Folder</p> </template>
                <template #icon> <ProiconsDelete class="h-4 w-4" /></template>
            </ButtonText>
        </div>
    </div>
</template>
