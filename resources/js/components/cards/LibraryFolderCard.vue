<script setup lang="ts">
import { type FolderResource } from '@/types/resources';

import { formatFileSize, handleStorageURL, toFormattedDate } from '@/service/util';
import { useTemplateRef } from 'vue';

import LibraryFolderCardMenu from '@/components/cards/LibraryFolderCardMenu.vue';
import BasePopover from '@/components/pinesUI/BasePopover.vue';
import ButtonIcon from '@/components/inputs/ButtonIcon.vue';

import ProiconsMoreVertical from '~icons/proicons/more-vertical';
import CircumShare1 from '~icons/circum/share-1';

const props = defineProps<{ data?: FolderResource }>();
const popover = useTemplateRef('popover');
</script>

<template>
    <div class="flex flex-col rounded-xl shadow-lg dark:bg-primary-dark-800/70 bg-white dark:hover:bg-primary-dark-600 hover:bg-primary-800 ring-1 ring-gray-900/5 w-full group">
        <RouterLink :to="`/${data?.path}`" class="w-full h-40 relative">
            <img
                class="w-full h-full object-cover rounded-t-md shadow-sm mb-auto ring-1 ring-gray-900/5"
                :src="handleStorageURL(data?.series?.thumbnail_url) ?? '/storage/thumbnails/default.webp'"
                alt="Folder Cover Art"
                loading="lazy"
            />
            <span class="w-full h-full hover:ring-[0.125rem] ring-inset ring-purple-600/90 absolute top-0 left-0 rounded-t-md"></span>
        </RouterLink>
        <section class="flex flex-1 h-full flex-col p-3 gap-2">
            <div class="flex items-start justify-between gap-1 flex-wrap xs:flex-nowrap">
                <h3 class="capitalize group-hover:text-purple-600">
                    {{ data?.series?.title ?? data?.name }}
                </h3>
                <span class="flex gap-2 [&>*]:h-6 text-sm">
                    <ButtonIcon :title="'Open Folder In New Tab'" :to="`/${data?.path}`" :class="`!aspect-[auto]`">
                        <template #icon><CircumShare1 class="h-4 w-4" /></template>
                    </ButtonIcon>
                    <BasePopover popoverClass="!max-w-56 rounded-lg mt-8" :buttonClass="'!p-1 ml-auto'" ref="popover">
                        <template #buttonIcon>
                            <ProiconsMoreVertical class="h-4 w-4" />
                        </template>
                        <template #content>
                            <LibraryFolderCardMenu :data="data" :handle-close-popover="popover?.handleClose" @clickAction="$emit('clickAction', data?.id)" />
                        </template>
                    </BasePopover>
                </span>
            </div>
            <span class="w-full text-sm text-neutral-600 dark:text-neutral-400 flex flex-col gap-1 h-full mt-auto" v-if="data">
                <span class="flex items-start justify-between flex-wrap mt-auto">
                    <p class="">Videos: {{ data?.file_count ?? '?' }}</p>
                </span>

                <span class="flex items-center justify-between gap-x-2 flex-wrap">
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
