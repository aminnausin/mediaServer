<script setup lang="ts">
import type { FolderResource } from '@/types/resources';

import { formatFileSize, handleStorageURL, toFormattedDate } from '@/service/util';
import { computed, ref, useTemplateRef } from 'vue';
import { setSeriesDownloadSettings } from '@/service/siteAPI';
import { useDashboardStore } from '@/stores/DashboardStore';
import { useQueryClient } from '@tanstack/vue-query';
import { BasePopover } from '@/components/cedar-ui/popover';
import { storeToRefs } from 'pinia';
import { ButtonIcon } from '@/components/cedar-ui/button';
import { toast } from '@aminnausin/cedar-ui';

import LibraryFolderCardMenu from '@/components/menus/LibraryFolderCardMenu.vue';
import TablerDownload from '@/components/icons/TablerDownload.vue';
import LazyImage from '@/components/lazy/LazyImage.vue';

import ProiconsMoreVertical from '~icons/proicons/more-vertical';
import CircumShare1 from '~icons/circum/share-1';

const { stateLibraryId, stateLibraries } = storeToRefs(useDashboardStore());

const props = defineProps<{ data: FolderResource }>();
const popover = useTemplateRef('popover');

const queryClient = useQueryClient();
const processing = ref(false);

const stateLibraryIsDownloadable = computed(() => {
    return stateLibraries.value.find((lib) => lib.id === stateLibraryId.value)?.downloads_enabled ?? false;
});

const handleToggleDownloads = async (id: number, currentValue: boolean) => {
    if (processing.value || !props.data?.id || currentValue !== props.data.series?.downloads_enabled) return;

    try {
        processing.value = true;

        await setSeriesDownloadSettings(id, { downloads_enabled: !currentValue });
        await queryClient.invalidateQueries({ queryKey: ['libraryFolders', stateLibraryId.value] });

        toast.success(`${currentValue ? 'Disabled' : 'Enabled'} Folder Downloads.`);
        processing.value = false;
    } catch (error) {
        toast('Failure', { type: 'danger', description: 'Unable to set download settings.' });
        console.error(error);
        processing.value = false;
    }
};
</script>

<template>
    <div class="data-card group flex w-full flex-col rounded-xl shadow-lg ring-1 ring-gray-900/5">
        <RouterLink :to="`/${encodeURI(data.path)}`" class="relative h-40 w-full">
            <LazyImage
                class="mb-auto h-full w-full rounded-t-md object-cover shadow-xs ring-1 ring-gray-900/5"
                :src="handleStorageURL(data?.series?.thumbnail_url) ?? '/storage/thumbnails/default.webp'"
                alt="Folder Cover Art"
            />

            <span
                class="ring-primary/90 absolute inset-0 flex h-full w-full flex-col items-end gap-2 rounded-t-xl p-2.5 transition duration-(--duration-input) ease-in-out ring-inset hover:ring-2"
            >
                <div
                    v-show="data.series?.downloads_enabled && stateLibraryIsDownloadable"
                    class="bg-surface-2 text-primary dark:text-foreground-0 ring-r-button size-7 shrink-0 rounded-full p-1 pt-0.5 ring-1"
                    title="is downloadable"
                >
                    <TablerDownload class="size-5" />
                </div>
            </span>
        </RouterLink>
        <section class="flex h-full flex-1 flex-col gap-2 p-3" v-if="data">
            <div class="xs:flex-nowrap flex flex-wrap items-start justify-between gap-1">
                <h3 class="group-hover:text-primary capitalize">{{ data.id }} - {{ data?.title ?? data?.name }}</h3>
                <span class="flex gap-2 text-sm *:h-6">
                    <ButtonIcon :title="'Open Folder In New Tab'" :to="`/${encodeURI(data.path)}`" :target="'_blank'" class="size-6 p-0">
                        <template #icon><CircumShare1 class="size-4" /></template>
                    </ButtonIcon>
                    <BasePopover popoverClass="max-w-56! rounded-lg mt-8" :buttonClass="'p-1! ml-auto'" ref="popover">
                        <template #buttonIcon>
                            <ProiconsMoreVertical class="size-4" />
                        </template>
                        <template #content>
                            <LibraryFolderCardMenu
                                :data="data"
                                :processing="processing"
                                :library-downloads-enabled="stateLibraryIsDownloadable"
                                :handle-close-popover="popover?.handleClose"
                                :handle-toggle-downloads="handleToggleDownloads"
                                @clickAction="$emit('clickAction', data?.id)"
                            />
                        </template>
                    </BasePopover>
                </span>
            </div>
            <span class="text-foreground-1 mt-auto flex h-full w-full flex-col gap-1 text-sm">
                <span class="mt-auto flex flex-wrap items-start justify-between">
                    <p class="">{{ 'Files' }}: {{ data?.file_count ?? '?' }}</p>
                </span>

                <span class="flex items-center justify-between gap-x-2">
                    <p class="truncate" :title="`Date Added ${toFormattedDate(data?.created_at)}`">
                        {{ toFormattedDate(data?.created_at) }}
                    </p>
                    <p class="whitespace-nowrap" :title="`Total Size ${formatFileSize(data.total_size)}`">
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
