<script setup lang="ts">
import type { VideoResource } from '@/contracts/media';

import { formatFileSize, toFormattedDate } from '@/service/util';
import { computed, ref } from 'vue';
import { MediaType } from '@/types/types';

import MediaTag from '@/components/labels/MediaTag.vue';

import ProiconsChevronRight from '~icons/proicons/chevron-right';

declare type Tab = 'metadata' | 'images' | 'subtitles' | 'fonts';

const props = defineProps<{ data: VideoResource }>();

const isAudio = computed(() => props.data.metadata?.media_type === MediaType.AUDIO);

const tabs = computed<Tab[]>(() => {
    const tabs: Tab[] = ['metadata', 'images'];

    if (isAudio.value) return tabs;

    if (props.data.subtitles.length) tabs.push('subtitles');
    if (props.data.fonts?.length) tabs.push('fonts');

    return tabs;
});
const activeTab = ref(tabs.value.at(0) ?? 'metadata');

const sumBy = <T,>(arr: T[] | undefined, fn: (item: T) => number): number => (arr ?? []).reduce((acc, item) => acc + fn(item), 0);

const videoInfo = computed(() => ({}));
const audioInfo = computed(() => ({}));

const metadataItems = computed<{ label: string; items: { label: string; value: any; to?: string }[] }[]>(() => {
    if (!props.data.metadata) return [];

    const mediaImages = props.data.metadata.images ?? [];
    const mediaImageSize = sumBy(mediaImages, (img) => img.size ?? 0);

    return [
        {
            label: 'Metadata',
            items: [
                { label: 'Title', value: props.data.title },
                props.data.title !== props.data.name && { label: 'Name', value: props.data.name },
                { label: 'Views', value: props.data.view_count },
                props.data.duration && { label: 'Duration', value: props.data.duration },
                ...(isAudio?.value
                    ? [
                          { label: 'Artist', value: props.data.artist },
                          { label: 'Album', value: props.data.album },
                          { label: 'Disc', value: props.data.season },
                          { label: 'Track', value: props.data.episode },
                      ]
                    : [
                          { label: 'Season', value: props.data.season },
                          { label: 'Episode', value: props.data.episode },
                      ]),
                props.data.released_at && { label: 'Released', value: props.data.released_at },
            ].filter(Boolean) as { label: string; value: any; to?: string }[],
        },

        !isAudio.value && {
            label: 'Video',
            items: [
                { label: 'Resolution', value: `${props.data.metadata.resolution_width}x${props.data.metadata.resolution_height}` },
                { label: 'Codec', value: props.data.metadata.codec },
            ],
        },
        {
            label: 'Audio',
            items: [{ label: 'Bitrate', value: props.data.metadata.bitrate }],
        },

        {
            label: 'Attachments',
            items: [
                { label: 'Images', value: props.data.metadata.images?.length },
                { label: 'Images Size', value: formatFileSize(mediaImageSize) },
                ...(!isAudio?.value
                    ? [
                          { label: 'Subtitles', value: props.data.subtitles.length },
                          { label: 'Fonts', value: props.data.fonts?.length },
                      ]
                    : []),
            ].filter(Boolean) as { label: string; value: any; to?: string }[],
        },
        {
            label: 'Timestamps',
            items: [
                { label: 'Created', value: toFormattedDate(props.data.created_at) },
                { label: 'Last Updated', value: toFormattedDate(props.data.updated_at) },
                { label: 'Last Edited', value: props.data.edited_at ? toFormattedDate(props.data.edited_at) : 'Never' },
            ],
        },
    ].filter(Boolean) as { label: string; items: { label: string; value: any; to?: string }[] }[];
});
</script>

<template>
    <div v-if="data" class="flex flex-col gap-4 text-sm">
        <template v-for="(group, index) in metadataItems" :key="index">
            <div class="flex flex-col gap-0.5">
                <p class="">{{ group.label }}</p>
                <div class="ms-4 flex flex-col gap-x-6">
                    <div v-for="item in group.items" :key="item.label" class="whitespace-pre-wrap">
                        <span class="text-foreground-1">{{ item.label }}: </span>&Tab;<span class="text-foreground-0">{{ item.value }}</span>
                    </div>

                    <div v-if="group.label === 'Metadata' && data.video_tags.length" title="Tags" class="flex gap-1 pt-4">
                        <span class="text-foreground-1">Tags: </span>
                        <span class="flex flex-wrap gap-1" v-if="data.video_tags.length">
                            <MediaTag v-for="tag in data.video_tags" :key="tag.id" class="text-foreground-0 pointer-events-none py-0.5 text-xs">
                                {{ tag.name }}
                            </MediaTag>
                        </span>
                    </div>

                    <details v-if="group.label === 'Metadata' && data.description" class="group flex w-full flex-col open:gap-2" hidden>
                        <summary class="hover:text-foreground-0 flex w-fit cursor-pointer items-center gap-1.5 transition-colors">
                            <ProiconsChevronRight class="-ms-1 size-3 transition-transform duration-200 group-open:rotate-90" />

                            Description
                        </summary>

                        <p class="scrollbar-minimal bg-surface-2 text-foreground-1 overflow-x-auto rounded-lg p-3 text-xs leading-relaxed whitespace-pre-wrap shadow-sm">
                            {{ data.description }}
                        </p>
                    </details>
                </div>
            </div>
        </template>
    </div>
</template>
