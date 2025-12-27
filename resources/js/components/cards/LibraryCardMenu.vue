<script setup lang="ts">
import type { CategoryResource, FolderResource } from '@/types/resources';

import { InputSelect } from '@/components/cedar-ui/select';
import { ButtonText } from '@/components/cedar-ui/button';

import InputLabel from '@/components/labels/InputLabel.vue';

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
            <h4 class="leading-none font-medium">Manage Library</h4>
            <p class="text-foreground-1 text-sm">Set Library Properties.</p>
        </div>
        <div class="flex flex-col gap-2 *:h-8 dark:*:bg-neutral-900">
            <div class="flex h-auto! flex-col gap-1 bg-transparent!">
                <InputLabel text="Default Folder" name="Default Folder" class="font-normal" />
                <InputSelect
                    id="default-folder"
                    root-class="flex-1 rounded-l-none capitalize w-full! whitespace-nowrap! col-span-2"
                    class="h-8! py-0 ps-2! dark:bg-neutral-900!"
                    :placeholder="'Select Default Folder'"
                    :default-item="folders.findIndex((folder) => folder.id == defaultFolder?.id) ?? 0"
                    :disabled="processing || !folders.length"
                    :title="'Select Default Folder'"
                    :menu-margin="{ top: 'mt-10', bottom: 'mb-10' }"
                    @selectItem="handleSetDefaultFolder"
                    :options="
                        folders.map((folder) => {
                            return { title: folder.title, value: folder.id };
                        })
                    "
                />
            </div>

            <ButtonText title="Scan for Changes" @click="handleStartScan(false)">
                <p class="flex-1 text-start">Scan Files</p>
                <template #icon> <ProiconsArrowSync class="size-4" /></template>
            </ButtonText>
            <ButtonText title="Verify File Metadata" @click="handleStartScan(true)">
                <p class="flex-1 text-start">Verify Files</p>
                <template #icon> <ProiconsArrowSync class="size-4" /></template>
            </ButtonText>
            <ButtonText title="Manage all Folders in Library" :to="`/dashboard/libraries/${data?.id}`" target="">
                <p class="flex-1 text-start">Manage Folders</p>
                <template #icon> <CircumFolderOn class="order-1 size-4" /></template>
            </ButtonText>
            <ButtonText :title="'Toggle Privacy'" @click="handleTogglePrivacy(data.id, data.is_private ?? false)" :disabled="processing">
                <p class="flex-1 text-start">{{ data.is_private ? 'Set to Public' : 'Set to Private' }}</p>
                <template #icon> <ProiconsLock v-if="data.is_private" class="size-4" /> <ProiconsLockOpen v-else class="size-4" /></template>
            </ButtonText>
            <ButtonText
                @click.stop.prevent="$emit('clickAction')"
                class="text-danger dark:text-foreground-0 dark:bg-danger-3! dark:hocus:bg-danger!"
                title="Remove From Server"
                disabled
            >
                <p class="flex-1 text-start">Remove Library</p>
                <template #icon> <ProiconsDelete class="size-4" /></template>
            </ButtonText>
        </div>
    </div>
</template>
