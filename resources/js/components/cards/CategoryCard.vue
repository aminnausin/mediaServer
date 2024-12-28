<script setup lang="ts">
import { type CategoryResource, type FolderResource } from '@/types/resources';

import { formatFileSize, toFormattedDate } from '@/service/util';
import { useQueryClient } from '@tanstack/vue-query';
import { updateCategory } from '@/service/mediaAPI.ts';
import { ref, useTemplateRef, watch } from 'vue';
import { toast } from '@/service/toaster/toastService';

import Popover from '@/components/pinesUI/Popover.vue';
import ButtonText from '@/components/inputs/ButtonText.vue';
import FormInputLabel from '@/components/labels/FormInputLabel.vue';
import InputSelect from '@/components/pinesUI/InputSelect.vue';

import ProiconsMoreVertical from '~icons/proicons/more-vertical';
import ProiconsArrowSync from '~icons/proicons/arrow-sync';
import ProiconsDelete from '~icons/proicons/delete';
import ProiconsLock from '~icons/proicons/lock';
import CircumEdit from '~icons/circum/edit';
import { startScanFilesTask } from '@/service/siteAPI';

const props = defineProps<{ data?: CategoryResource }>();
const defaultFolder = ref<FolderResource>();
const queryClient = useQueryClient();
const processing = ref(false);

const popover = useTemplateRef('popover');

const handleSetDefaultFolder = async (newFolder: { value: number }) => {
    if (processing.value || !props.data?.id || newFolder.value === props.data.default_folder_id) return;

    try {
        processing.value = true;

        await updateCategory(props.data.id, { default_folder_id: newFolder.value });
        toast('Default Folder Updated', { type: 'success' });

        await queryClient.invalidateQueries({
            queryKey: ['categories'],
        });

        processing.value = false;
    } catch (error) {
        console.log(error);
        toast('Update Failed', { type: 'danger' });
    }
};

const handleStartScan = async () => {
    if (!props.data?.id) {
        toast('Error', { description: 'Invalid Category ID!', type: 'danger' });
        popover.value?.handleClose();
        return;
    }

    try {
        await startScanFilesTask(props.data.id);
        toast.add('Success', { type: 'success', description: `Submitted scan Request!` });
        popover.value?.handleClose();
    } catch (error) {
        toast('Failure', { type: 'danger', description: `Unable to submit scan request.` });
    }
};

watch(
    () => props.data,
    () => {
        if (!props.data?.folders || props.data.folders.length < 1) return;

        defaultFolder.value = props.data.default_folder_id
            ? props.data.folders.find((folder) => folder.id === props.data?.default_folder_id)
            : props.data.folders[0];
    },
    { immediate: true },
);
</script>

<template>
    <div class="flex flex-col rounded-xl shadow-lg dark:bg-primary-dark-800/70 bg-white ring-1 ring-gray-900/5 w-full">
        <img
            id="folder-thumbnail"
            class="w-full h-40 object-cover rounded-t-md shadow-md mb-auto relative group ring-1 ring-gray-900/5"
            :src="
                defaultFolder?.series?.thumbnail_url ??
                'https://m.media-amazon.com/images/M/MV5BMjVjZGU5ZTktYTZiNC00N2Q1LThiZjMtMDVmZDljN2I3ZWIwXkEyXkFqcGdeQXVyMTUzMTg2ODkz._V1_.jpg'
            "
            alt="Folder Cover Art"
            loading="lazy"
        />
        <section class="flex flex-1 h-full flex-col p-4 gap-2">
            <h3 class="capitalize text-lg flex items-start justify-between flex-wrap">
                {{ data?.name }}

                <span class="flex flex-wrap gap-2 [&>*]:h-6 text-sm">
                    <ButtonText class="dark:!bg-neutral-950 hidden 2xl:flex" :title="'Scan for Folder Changes'" @click="handleStartScan">
                        <template #text> Scan </template>
                        <template #icon> <ProiconsArrowSync class="h-4 w-4" /></template>
                    </ButtonText>
                    <Popover popoverClass="w-48 sm:w-64 rounded-lg" :buttonClass="'!p-1 ml-auto'" ref="popover">
                        <template #buttonIcon>
                            <ProiconsMoreVertical class="h-4 w-4 2xl:hidden" />
                            <CircumEdit class="w-full h-full hidden 2xl:block" />
                        </template>
                        <template #content>
                            <div class="grid gap-4">
                                <div class="space-y-2">
                                    <h4 class="font-medium leading-none">Manage Library</h4>
                                    <p class="text-sm text-muted-foreground">Set Library Properties.</p>
                                </div>

                                <div class="grid gap-2">
                                    <div class="w-full flex flex-col gap-1">
                                        <FormInputLabel :field="{ text: 'Default Folder', name: 'Default Folder' }" class="font-normal" />
                                        <InputSelect
                                            id="default_folder"
                                            root-class="flex-1 rounded-l-none capitalize !w-full !whitespace-nowrap col-span-2"
                                            class="h-8"
                                            :placeholder="'Select Default Folder'"
                                            :default-item="data?.folders.findIndex((folder) => folder.id == defaultFolder?.id) ?? 0"
                                            :disabled="processing"
                                            title="Select Default Folder"
                                            @selectItem="handleSetDefaultFolder"
                                            :options="
                                                data?.folders.map((folder) => {
                                                    return { title: folder.name, value: folder.id };
                                                })
                                            "
                                        />
                                    </div>
                                    <ButtonText
                                        class="h-8 dark:!bg-neutral-950 2xl:hidden"
                                        :title="'Scan for Folder Changes'"
                                        @click="handleStartScan"
                                    >
                                        <template #text> Scan Folders </template>
                                        <template #icon> <ProiconsArrowSync class="h-4 w-4" /></template>
                                    </ButtonText>
                                    <ButtonText
                                        class="h-8 dark:!bg-neutral-950 disabled:opacity-60"
                                        :title="'Set User Access Permissions'"
                                        disabled
                                    >
                                        <template #text> Manage Access </template>
                                        <template #icon> <ProiconsLock class="h-4 w-4" /></template>
                                    </ButtonText>
                                    <ButtonText
                                        class="h-8 text-rose-600 dark:!bg-rose-700 disabled:opacity-60"
                                        :title="'Remove From Server'"
                                        @click.stop.prevent="$emit('clickAction')"
                                        disabled
                                    >
                                        <template #text> Remove Library </template>
                                        <template #icon> <ProiconsDelete class="h-4 w-4" /></template>
                                    </ButtonText>
                                </div>
                            </div>
                        </template>
                    </Popover>
                </span>
            </h3>
            <span class="w-full text-sm text-neutral-500 dark:text-neutral-400" v-if="data">
                <span class="flex items-center justify-between mt-auto flex-wrap">
                    <p class="">Folders: {{ data?.folders_count }}</p>
                    <p class="hidden sm:block" title="Total Size">
                        {{ formatFileSize(data.folders.reduce((total, folder) => total + Number(folder.total_size), 0)) }}
                    </p>
                </span>
                <span class="flex items-center justify-between gap-x-2 flex-wrap">
                    <p class="">{{ defaultFolder ? `Default: ${defaultFolder.name}` : 'No Default Folder' }}</p>
                    <p class="hidden sm:block" title="Date Added">
                        {{ data?.created_at ? toFormattedDate(new Date(data?.created_at + ' EST')) : 'N/A' }}
                    </p>
                </span>
                <span class="sm:hidden flex items-center justify-between gap-x-2 flex-wrap mt-2">
                    <p class="" title="Date Added">
                        {{ data?.created_at ? toFormattedDate(new Date(data?.created_at + ' EST')) : 'N/A' }}
                    </p>
                    <p class="" title="Total Size">
                        {{ formatFileSize(data.folders.reduce((total, folder) => total + Number(folder.total_size), 0)) }}
                    </p>
                </span>
            </span>
        </section>
    </div>
</template>
