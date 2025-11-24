<script setup lang="ts">
import { type CategoryResource, type FolderResource } from '@/types/resources';

import { formatFileSize, handleStorageURL, toFormattedDate } from '@/service/util';
import { startScanFilesTask, startVerifyFilesTask, toggleCategoryPrivacy } from '@/service/siteAPI';
import { computed, ref, useTemplateRef, watch } from 'vue';
import { useQueryClient } from '@tanstack/vue-query';
import { updateCategory } from '@/service/mediaAPI.ts';
import { toast } from '@/service/toaster/toastService';

import LibraryCardMenu from '@/components/cards/LibraryCardMenu.vue';
import BasePopover from '@/components/pinesUI/BasePopover.vue';

import ProiconsMoreVertical from '~icons/proicons/more-vertical';

const props = defineProps<{ data?: CategoryResource }>();
const defaultFolder = ref<FolderResource>();
const queryClient = useQueryClient();
const processing = ref(false);

const popover = useTemplateRef('popover');

const folders = computed(() => {
    const foldersCopy = [...(props.data?.folders || [])];
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
        toast.add('Success', { type: 'success', description: `Submitted ${verifyOnly ? 'verify' : 'scan'} request!` });
        popover.value?.handleClose();
    } catch (error) {
        toast('Failure', { type: 'danger', description: `Unable to submit ${verifyOnly ? 'verify' : 'scan'} request.` });
        console.error(error);
    }
};

const handleTogglePrivacy = async (id: number, currentValue: boolean) => {
    if (processing.value || !props.data?.id || currentValue !== props.data.is_private) return;

    try {
        processing.value = true;

        const res = await toggleCategoryPrivacy(id, !currentValue);

        if (res?.status !== 403) toast.success(`Library set to ${currentValue ? 'Public' : 'Private'}.`);
        else {
            toast.error('You do not have permission to set the privacy of libraries.');
        }

        await queryClient.invalidateQueries({ queryKey: ['categories'] });

        processing.value = false;
    } catch (error) {
        toast('Failure', { type: 'danger', description: `Unable to set privacy.` });
        console.error(error);
        processing.value = false;
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
        <RouterLink :to="`/${data?.name}`" class="w-full h-40 relative peer">
            <img
                class="w-full h-full object-cover rounded-t-md shadow-sm mb-auto ring-1 ring-gray-900/5 hover:ring-4 hover:ring-red-500 ring-inset peer"
                :src="handleStorageURL(defaultFolder?.series?.thumbnail_url) ?? '/storage/thumbnails/default.webp'"
                alt="Folder Cover Art"
                loading="lazy"
            />
            <span class="w-full h-full hover:ring-[0.125rem] ring-inset ring-purple-600/90 absolute top-0 left-0 rounded-t-md"></span>
        </RouterLink>
        <section class="flex flex-1 h-full flex-col p-3 gap-2">
            <div class="flex items-start justify-between flex-wrap">
                <h3 class="capitalize group-hover:text-purple-600">
                    {{ data?.name }}
                </h3>
                <span class="flex gap-2 [&>*]:h-6 text-sm">
                    <BasePopover popoverClass="!max-w-56 rounded-lg mt-8" :buttonClass="'!p-1 ml-auto'" ref="popover">
                        <template #buttonIcon>
                            <ProiconsMoreVertical class="h-4 w-4" />
                        </template>
                        <template #content>
                            <LibraryCardMenu
                                :data="data"
                                :folders="folders"
                                :default-folder="defaultFolder"
                                :processing="processing"
                                :handle-set-default-folder="handleSetDefaultFolder"
                                :handle-start-scan="handleStartScan"
                                :handle-toggle-privacy="handleTogglePrivacy"
                            />
                        </template>
                    </BasePopover>
                </span>
            </div>
            <span class="w-full text-sm text-neutral-600 dark:text-neutral-400 flex flex-col sm:gap-1 h-full mt-auto" v-if="data">
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
