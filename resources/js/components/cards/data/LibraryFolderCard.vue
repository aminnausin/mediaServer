<script setup lang="ts">
import { type FolderResource } from '@/types/resources';

import { formatFileSize, handleStorageURL, toFormattedDate } from '@/service/util';
import { useTemplateRef } from 'vue';
import { ButtonIcon } from '@/components/cedar-ui/button';

import LibraryFolderCardMenu from '@/components/menus/LibraryFolderCardMenu.vue';
import BasePopover from '@/components/pinesUI/BasePopover.vue';

import ProiconsMoreVertical from '~icons/proicons/more-vertical';
import CircumShare1 from '~icons/circum/share-1';

defineProps<{ data: FolderResource }>();
const popover = useTemplateRef('popover');
</script>

<template>
    <div class="data-card group flex w-full flex-col rounded-xl shadow-lg ring-1 ring-gray-900/5">
        <RouterLink :to="`/${encodeURI(data.path)}`" class="relative h-40 w-full">
            <img
                class="mb-auto h-full w-full rounded-t-md object-cover shadow-xs ring-1 ring-gray-900/5"
                :src="handleStorageURL(data?.series?.thumbnail_url) ?? '/storage/thumbnails/default.webp'"
                alt="Folder Cover Art"
                loading="lazy"
            />
            <span class="ring-primary/90 absolute top-0 left-0 h-full w-full rounded-t-md ring-inset hover:ring-2"></span>
        </RouterLink>
        <section class="flex h-full flex-1 flex-col gap-2 p-3">
            <div class="xs:flex-nowrap flex flex-wrap items-start justify-between gap-1">
                <h3 class="group-hover:text-primary capitalize">
                    {{ data?.title ?? data?.name }}
                </h3>
                <span class="flex gap-2 text-sm *:h-6">
                    <ButtonIcon :title="'Open Folder In New Tab'" :to="`/${encodeURI(data.path)}`" class="size-6 p-0">
                        <template #icon><CircumShare1 class="size-4" /></template>
                    </ButtonIcon>
                    <BasePopover popoverClass="max-w-56! rounded-lg mt-8" :buttonClass="'p-1! ml-auto'" ref="popover">
                        <template #buttonIcon>
                            <ProiconsMoreVertical class="size-4" />
                        </template>
                        <template #content>
                            <LibraryFolderCardMenu :data="data" :handle-close-popover="popover?.handleClose" @clickAction="$emit('clickAction', data?.id)" />
                        </template>
                    </BasePopover>
                </span>
            </div>
            <span class="text-foreground-1 mt-auto flex h-full w-full flex-col gap-1 text-sm" v-if="data">
                <span class="mt-auto flex flex-wrap items-start justify-between">
                    <p class="">{{ data.is_majority_audio ? 'Tracks' : 'Videos' }}: {{ data?.file_count ?? '?' }}</p>
                </span>

                <span class="flex flex-wrap items-center justify-between gap-x-2">
                    <p class="" :title="`Date Added ${data?.created_at ? toFormattedDate(new Date(data?.created_at)) : 'N/A'}`">
                        {{ data?.created_at ? toFormattedDate(new Date(data?.created_at)) : 'N/A' }}
                    </p>
                    <p class="" :title="`Total Size ${formatFileSize(data.total_size)}`">
                        <!-- {{ formatFileSize(data.total_size) }} -->
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
