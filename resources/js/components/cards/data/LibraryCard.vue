<script setup lang="ts">
import type { CategoryResource, FolderResource } from '@/types/resources';

import { startScanFilesTask, startVerifyFilesTask, toggleCategoryPrivacy } from '@/service/siteAPI';
import { formatFileSize, handleStorageURL, toFormattedDate } from '@/service/util';
import { computed, ref, useTemplateRef, watch } from 'vue';
import { useQueryClient } from '@tanstack/vue-query';
import { updateCategory } from '@/service/mediaAPI.ts';
import { BasePopover } from '@/components/cedar-ui/popover';
import { toast } from '@aminnausin/cedar-ui';

import LibraryCardMenu from '@/components/menus/LibraryCardMenu.vue';
import LazyImage from '@/components/lazy/LazyImage.vue';

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

        await updateCategory(props.data.id, { default_folder_id: newFolder.value });

        await queryClient.invalidateQueries({
            queryKey: ['categories'],
        });

        toast.success(`Default folder set to ${defaultFolder.value?.title}.`);

        processing.value = false;
    } catch (error) {
        console.log(error);
        toast('Unable to set Default Folder', { type: 'danger', description: `${error}` });

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

        await toggleCategoryPrivacy(id, !currentValue);
        await queryClient.invalidateQueries({ queryKey: ['categories'] });

        toast.success(`Library set to ${currentValue ? 'Public' : 'Private'}.`);
        processing.value = false;
    } catch (error) {
        toast('Failure', { type: 'danger', description: 'Unable to set privacy. You may not have permission to set the privacy of libraries.' });
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
    <div class="data-card group flex w-full flex-col rounded-xl shadow-lg ring-1 ring-gray-900/5">
        <RouterLink :to="`/${data?.name}`" class="peer relative h-40 w-full">
            <LazyImage
                class="peer mb-auto h-full w-full rounded-t-xl object-cover shadow-xs ring-1 ring-gray-900/5 ring-inset hover:ring-4"
                :src="handleStorageURL(defaultFolder?.series?.thumbnail_url) ?? '/storage/thumbnails/default.webp'"
                alt="Folder Cover Art"
            />
            <span class="ring-primary/90 absolute top-0 left-0 h-full w-full rounded-t-xl transition duration-(--duration-input) ease-in-out ring-inset hover:ring-2"></span>
        </RouterLink>
        <section class="flex h-full flex-1 flex-col gap-2 p-3">
            <div class="flex flex-wrap items-start justify-between">
                <h3 class="group-hover:text-primary capitalize">{{ data?.id }} - {{ data?.name }}</h3>
                <span class="flex gap-2 text-sm *:h-6">
                    <BasePopover popoverClass="max-w-56! rounded-lg mt-8" :buttonClass="'p-1! ml-auto'" ref="popover">
                        <template #buttonIcon>
                            <ProiconsMoreVertical class="size-4" />
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
            <span class="text-foreground-1 mt-auto flex h-full w-full flex-col text-sm sm:gap-1" v-if="data">
                <span class="flex flex-wrap items-start justify-between">
                    <span class="flex flex-col gap-1 sm:gap-0">
                        <p class="">Files: {{ data?.videos_count ?? '?' }}</p>

                        <p class="">Folders: {{ data?.folders_count }}</p>
                    </span>
                    <p class="hidden sm:block" :title="`Total Size ${formatFileSize(data.total_size)}`">
                        {{ formatFileSize(data.total_size) }}
                    </p>
                    <p class="sm:hidden">{{ defaultFolder ? `Default Folder: ${defaultFolder.name}` : 'No Default Folder' }}</p>
                </span>
                <span class="mt-auto hidden items-center justify-between gap-x-2 truncate sm:flex">
                    <p class="">{{ defaultFolder ? `Default: ${defaultFolder.name}` : 'No Default Folder' }}</p>
                    <p class="truncate" :title="`Date Added ${data?.created_at ? toFormattedDate(new Date(data?.created_at + ' EST')) : 'N/A'}`">
                        {{ data?.created_at ? toFormattedDate(new Date(data?.created_at + ' EST')) : 'N/A' }}
                    </p>
                </span>
                <span class="flex flex-wrap items-center justify-between gap-x-2 pt-1 sm:hidden sm:pt-0">
                    <p class="" :title="`Date Added ${data?.created_at ? toFormattedDate(new Date(data?.created_at + ' EST')) : 'N/A'}`">
                        {{ toFormattedDate(data?.created_at) }}
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
