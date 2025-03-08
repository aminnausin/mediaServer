<script setup lang="ts">
import { type CategoryResource, type FolderResource } from '@/types/resources';

import { formatFileSize, handleStorageURL, toFormattedDate } from '@/service/util';
import { startScanFilesTask, startVerifyFilesTask } from '@/service/siteAPI';
import { computed, ref, useTemplateRef, watch } from 'vue';
import { useQueryClient } from '@tanstack/vue-query';
import { updateCategory } from '@/service/mediaAPI.ts';
import { toast } from '@/service/toaster/toastService';

import FormInputLabel from '@/components/labels/FormInputLabel.vue';
import InputSelect from '@/components/pinesUI/InputSelect.vue';
import ButtonIcon from '@/components/inputs/ButtonIcon.vue';
import ButtonText from '@/components/inputs/ButtonText.vue';
import Popover from '@/components/pinesUI/Popover.vue';

import ProiconsMoreVertical from '~icons/proicons/more-vertical';
import ProiconsArrowSync from '~icons/proicons/arrow-sync';
import CircumFolderOn from '~icons/circum/folder-on';
import ProiconsDelete from '~icons/proicons/delete';
import ProiconsLock from '~icons/proicons/lock';
import CircumShare1 from '~icons/circum/share-1';

const props = defineProps<{ data?: CategoryResource }>();
const defaultFolder = ref<FolderResource>();
const queryClient = useQueryClient();
const processing = ref(false);

const popover = useTemplateRef('popover');

const folders = computed(() => {
    let foldersCopy = props.data?.folders ? [...props.data?.folders] : [];
    return foldersCopy.sort((itemA, itemB) => itemA.name.localeCompare(itemB.name));
});

const handleSetDefaultFolder = async (newFolder: { value: number }) => {
    if (processing.value || !props.data?.id || newFolder.value === props.data.default_folder_id) return;

    try {
        processing.value = true;

        const res = await updateCategory(props.data.id, { default_folder_id: newFolder.value });

        if (res?.status !== 403) toast('Default Folder Updated', { type: 'success' });
        else {
            toast('Unable to set Default Folder', { type: 'danger' });
        }
        await queryClient.invalidateQueries({
            queryKey: ['categories'],
        });

        processing.value = false;
    } catch (error) {
        console.log(error);
        toast('Update Failed', { type: 'danger' });
        processing.value = false;
    }
};

const handleStartScan = async (verifyOnly: boolean = false) => {
    if (!props.data?.id) {
        toast('Error', { description: 'Invalid Category ID!', type: 'danger' });
        popover.value?.handleClose();
        return;
    }

    try {
        if (verifyOnly) await startVerifyFilesTask(props.data.id);
        else await startScanFilesTask(props.data.id);
        toast.add('Success', { type: 'success', description: `Submitted ${verifyOnly ? 'verify' : 'scan'} Request!` });
        popover.value?.handleClose();
    } catch (error) {
        toast('Failure', { type: 'danger', description: `Unable to submit ${verifyOnly ? 'verify' : 'scan'} request.` });
    }
};

watch(
    () => props.data,
    () => {
        if (!props.data?.folders || props.data.folders.length < 1) return;

        defaultFolder.value = props.data.default_folder_id ? props.data.folders.find((folder) => folder.id === props.data?.default_folder_id) : props.data.folders[0];
    },
    { immediate: true },
);
</script>

<template>
    <div class="flex flex-col rounded-xl shadow-lg dark:bg-primary-dark-800/70 bg-white dark:hover:bg-primary-dark-600 hover:bg-primary-800 ring-1 ring-gray-900/5 w-full group">
        <RouterLink :to="`/${data?.name}`" class="w-full h-40">
            <img
                class="w-full h-full object-cover rounded-t-md shadow-sm mb-auto ring-1 ring-gray-900/5"
                :src="handleStorageURL(defaultFolder?.series?.thumbnail_url) ?? '/storage/thumbnails/default.webp'"
                alt="Folder Cover Art"
            />
        </RouterLink>
        <section class="flex flex-1 h-full flex-col p-3 gap-2">
            <div class="flex items-start justify-between flex-wrap">
                <h3 class="capitalize group-hover:text-purple-600">
                    {{ data?.name }}
                </h3>
                <span class="flex gap-2 [&>*]:h-6 text-sm">
                    <!-- <ButtonText class="hidden 2xl:flex" :title="'Scan for Folder Changes'" @click="handleStartScan">
                        <template #text> Scan </template>
                        <template #icon> <ProiconsArrowSync class="h-4 w-4" /></template>
                    </ButtonText> -->
                    <ButtonIcon :title="'Open Library In New Tab'" :to="`/${data?.name}`" :class="`!aspect-[auto]`">
                        <template #icon><CircumShare1 class="h-4 w-4" /></template>
                    </ButtonIcon>
                    <Popover popoverClass="!max-w-56 rounded-lg" :buttonClass="'!p-1 ml-auto'" ref="popover">
                        <template #buttonIcon>
                            <ProiconsMoreVertical class="h-4 w-4" />
                        </template>
                        <template #content>
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <h4 class="font-medium leading-none">Manage Library</h4>
                                    <p class="text-sm text-muted-foreground">Set Library Properties.</p>
                                </div>

                                <div class="space-y-2 [&>*]:w-full">
                                    <div class="flex flex-col gap-1">
                                        <FormInputLabel :field="{ text: 'Default Folder', name: 'Default Folder' }" class="font-normal" />
                                        <InputSelect
                                            id="default_folder"
                                            root-class="flex-1 rounded-l-none capitalize !w-full !whitespace-nowrap col-span-2"
                                            class="h-8"
                                            :placeholder="'Select Default Folder'"
                                            :default-item="folders.findIndex((folder) => folder.id == defaultFolder?.id) ?? 0"
                                            :disabled="processing || !folders.length"
                                            :title="'Select Default Folder'"
                                            @selectItem="handleSetDefaultFolder"
                                            :options="
                                                folders.map((folder) => {
                                                    return { title: folder.name, value: folder.id };
                                                })
                                            "
                                        />
                                    </div>

                                    <ButtonText class="h-8 dark:!bg-neutral-950" :title="'Scan for Folder Changes'" @click="handleStartScan">
                                        <template #text> Scan Folders </template>
                                        <template #icon> <ProiconsArrowSync class="h-4 w-4" /></template>
                                    </ButtonText>
                                    <ButtonText class="h-8 dark:!bg-neutral-950" :title="'Verify File Metadata'" @click="handleStartScan">
                                        <template #text> Verify Folders </template>
                                        <template #icon> <ProiconsArrowSync class="h-4 w-4" /></template>
                                    </ButtonText>
                                    <ButtonText class="h-8 dark:!bg-neutral-950" :title="'Manage all Folders in Library'" :to="`/dashboard/libraries/${data?.id}`" target="">
                                        <template #text> Manage Folders </template>
                                        <template #icon> <CircumFolderOn class="h-4 w-4" /></template>
                                    </ButtonText>
                                    <ButtonText class="h-8 dark:!bg-neutral-950 disabled:opacity-60" :title="'Set User Access Permissions'" disabled>
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
            </div>
            <span class="w-full text-sm text-neutral-500 dark:text-neutral-400 flex flex-col sm:gap-1 h-full mt-auto" v-if="data">
                <span class="flex items-start justify-between flex-wrap">
                    <span class="flex flex-col gap-1 sm:gap-0">
                        <p class="">Videos: {{ data?.videos_count ?? '?' }}</p>

                        <p class="">Folders: {{ data?.folders_count }}</p>
                    </span>
                    <p class="hidden sm:block" :title="`Total Size ${formatFileSize(data.total_size)}`">
                        {{ formatFileSize(data.total_size) }}
                    </p>
                    <p class="sm:hidden">{{ defaultFolder ? `Default Folder: ${defaultFolder.name}` : 'No Default Folder' }}</p>
                </span>
                <span class="hidden sm:flex items-center justify-between gap-x-2 mt-auto truncate">
                    <p class="">{{ defaultFolder ? `Default: ${defaultFolder.name}` : 'No Default Folder' }}</p>
                    <p class="truncate" :title="`Date Added ${data?.created_at ? toFormattedDate(new Date(data?.created_at + ' EST')) : 'N/A'}`">
                        {{ data?.created_at ? toFormattedDate(new Date(data?.created_at + ' EST')) : 'N/A' }}
                    </p>
                </span>
                <span class="sm:hidden flex items-center justify-between gap-x-2 flex-wrap pt-1 sm:pt-0">
                    <p class="" :title="`Date Added ${data?.created_at ? toFormattedDate(new Date(data?.created_at + ' EST')) : 'N/A'}`">
                        {{ data?.created_at ? toFormattedDate(new Date(data?.created_at + ' EST')) : 'N/A' }}
                    </p>
                    <p class="" :title="`Total Size ${formatFileSize(data.total_size)}`">
                        {{ formatFileSize(data.total_size) }}
                    </p>
                </span>
            </span>
        </section>
    </div>
</template>

<style lang="css" scoped>
img {
    image-rendering: auto;
    image-rendering: crisp-edges;
    image-rendering: pixelated;

    /* Safari seems to support, but seems deprecated and does the same thing as the others. */
    image-rendering: -webkit-optimize-contrast;
}
</style>
