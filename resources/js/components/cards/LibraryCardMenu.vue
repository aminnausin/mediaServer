<script setup lang="ts">
import type { CategoryResource, FolderResource } from '@/types/resources';

import FormInputLabel from '@/components/labels/FormInputLabel.vue';
import InputSelect from '@/components/pinesUI/InputSelect.vue';
import ButtonText from '@/components/inputs/ButtonText.vue';

import ProiconsArrowSync from '~icons/proicons/arrow-sync';
import ProiconsLockOpen from '~icons/proicons/lock-open';
import CircumFolderOn from '~icons/circum/folder-on';
import ProiconsDelete from '~icons/proicons/delete';
import ProiconsLock from '~icons/proicons/lock';

const props = withDefaults(
    defineProps<{
        data?: CategoryResource;
        folders: FolderResource[];
        defaultFolder?: FolderResource;
        processing: boolean;
        handleSetDefaultFolder: (newFolder: { value: number }) => Promise<void>;
        handleStartScan: (verifyOnly?: boolean) => Promise<void>;
        handleTogglePrivacy: (id: number, currentValue: boolean) => Promise<void>;
    }>(),
    {},
);
</script>

<template>
    <div class="space-y-4" v-if="data">
        <div class="space-y-2">
            <h4 class="font-medium leading-none">Manage Library</h4>
            <p class="text-sm text-muted-foreground">Set Library Properties.</p>
        </div>
        <div class="[&>*]:w-full grid gap-2 [&>*]:dark:bg-neutral-900 [&>*]:h-8 [&>*]:disabled:opacity-60">
            <div class="flex flex-col gap-1 !h-auto !bg-transparent">
                <FormInputLabel :field="{ text: 'Default Folder', name: 'Default Folder' }" class="font-normal" />
                <InputSelect
                    id="default-folder"
                    root-class="flex-1 rounded-l-none capitalize !w-full !whitespace-nowrap col-span-2 "
                    :class="'h-8 dark:!bg-neutral-900'"
                    :placeholder="'Select Default Folder'"
                    :default-item="folders.findIndex((folder) => folder.id == defaultFolder?.id) ?? 0"
                    :disabled="processing || !folders.length"
                    :title="'Select Default Folder'"
                    :menu-margin="{ top: 'mt-10', bottom: 'mb-10' }"
                    @selectItem="handleSetDefaultFolder"
                    :options="
                        folders.map((folder) => {
                            return { title: folder.name, value: folder.id };
                        })
                    "
                />
            </div>

            <ButtonText :title="'Scan for Changes'" @click="handleStartScan(false)">
                <template #text> <p class="flex-1 text-start">Scan Files</p> </template>
                <template #icon> <ProiconsArrowSync class="h-4 w-4" /></template>
            </ButtonText>
            <ButtonText :title="'Verify File Metadata'" @click="handleStartScan(true)">
                <template #text> <p class="flex-1 text-start">Verify Files</p> </template>
                <template #icon> <ProiconsArrowSync class="h-4 w-4" /></template>
            </ButtonText>
            <ButtonText :title="'Manage all Folders in Library'" :to="`/dashboard/libraries/${data?.id}`" target="">
                <template #text> <p class="flex-1 text-start">Manage Folders</p> </template>
                <template #icon> <CircumFolderOn class="order-1 h-4 w-4" /></template>
            </ButtonText>
            <ButtonText :title="'Toggle Privacy'" @click="handleTogglePrivacy(data.id, data.is_private ?? false)" :disabled="processing">
                <template #text>
                    <p class="flex-1 text-start">{{ data.is_private ? 'Set to Public' : 'Set to Private' }}</p>
                </template>
                <template #icon> <ProiconsLock v-if="data.is_private" class="h-4 w-4" /> <ProiconsLockOpen v-else class="h-4 w-4" /></template>
            </ButtonText>
            <ButtonText class="text-rose-600 dark:!bg-rose-700" :title="'Remove From Server'" text="Remove Library" @click.stop.prevent="$emit('clickAction')" disabled>
                <template #icon> <ProiconsDelete class="h-4 w-4" /></template>
            </ButtonText>
        </div>
    </div>
</template>
