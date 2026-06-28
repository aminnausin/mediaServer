<script setup lang="ts">
import type { FolderResource } from '@/contracts/media';
import type { ComputedRef, Ref } from 'vue';

import { formatFileSize, toFormattedDate, toFormattedDuration } from '@/service/util';
import { computed, inject } from 'vue';

import FolderInfoRow from '@/components/folders/FolderInfoRow.vue';
import FolderTab from '@/components/folders/FolderTab.vue';
import MediaTag from '@/components/labels/MediaTag.vue';

import ProiconsChevronRight from '~icons/proicons/chevron-right';

const isAudio = inject<ComputedRef<boolean>>('isAudio');
const data = inject<Ref<FolderResource>>('data');

const metadataItems = computed<{ label: string; items: { label: string; value: any; to?: string }[] }[]>(() => {
    if (!data?.value.series) return [];
    return [
        {
            label: 'Series',
            items: [
                { label: 'Title', value: data.value.series.title },
                { label: 'Studio', value: data.value.series.studio },
                { label: 'Score', value: data.value.series.rating ? data.value.series.rating + '%' : undefined },
                ...(isAudio?.value
                    ? [
                          { label: 'Discs', value: data.value.series.seasons },
                          { label: 'Tracks', value: data.value.series.episodes },
                      ]
                    : [
                          { label: 'Seasons', value: data.value.series.seasons },
                          { label: 'Episodes', value: data.value.series.episodes },
                          { label: 'Films', value: data.value.series.films },
                      ]),
                { label: 'Avg Intro', value: toFormattedDuration(data.value.series.avg_intro_duration) },
                { label: 'Started', value: data.value.series.started_at },
                { label: 'Ended', value: data.value.series.ended_at ?? (data.value.series.started_at ? 'Ongoing' : undefined) },
            ],
        },
        {
            label: 'Library',
            items: [
                { label: 'Files', value: data.value.file_count },
                data.value.file_count !== data.value.episodes && { label: 'In Series', value: data.value.episodes },
                { label: 'Total Size', value: formatFileSize(data.value.total_size ?? 0) },
                { label: 'Type', value: data.value.is_majority_audio ? 'Audio' : 'Video' },
                { label: 'Path', value: data.value.path, to: `/${data.value.category_id}/${data.value.title}` },
                { label: 'ID', value: data.value.id },
                { label: 'UUID', value: data.value.series.uuid },
                { label: 'Library ID', value: data.value.category_id, to: `/dashboard/libraries/${data.value.category_id}` },
            ].filter(Boolean) as { label: string; value: any; to?: string }[],
        },
        {
            label: 'Attachments',
            items: [
                { label: 'Images', value: data.value.series.images.length },
                { label: 'Media Images', value: data.value.videos.reduce((acc, vid) => (acc += vid.metadata?.images?.length ?? 0), 0) },
                { label: 'Attachments', value: 0 },
                !isAudio?.value && { label: 'Subtitles', value: data.value.videos.reduce((acc, vid) => (acc += vid.subtitles.length ?? 0), 0) },
            ].filter(Boolean) as { label: string; value: any; to?: string }[],
        },
        {
            label: 'Timestamps',
            items: [
                { label: 'Created', value: toFormattedDate(data.value.created_at) },
                { label: 'Last Updated', value: toFormattedDate(data.value.updated_at) },
                { label: 'Last Edited', value: toFormattedDate(data.value.edited_at) },
            ],
        },
    ];
});
</script>

<template>
    <FolderTab v-if="data" class="text-xs">
        <template v-for="(group, index) in metadataItems" :key="index">
            <div class="flex flex-col gap-0.5">
                <p class="text-foreground-1 font-semibold uppercase">{{ group.label }}</p>
                <div class="flex flex-wrap gap-x-6 gap-y-3">
                    <FolderInfoRow
                        v-for="item in group.items"
                        class="text-foreground-1"
                        :key="item.label"
                        :title="item.label"
                        :value="item.value"
                        :value-class="'text-sm text-foreground-0'"
                        :to="item.to"
                    />

                    <FolderInfoRow v-if="group.label === 'Series' && data.series?.folder_tags?.length" title="Tags" class="text-foreground-1">
                        <div class="flex flex-wrap gap-1 pt-0.5" v-if="data.series.folder_tags?.length">
                            <MediaTag v-for="tag in data.series.folder_tags" :key="tag.id" class="bg-surface-3! text-foreground-0 py-0.5 text-xs capitalize">
                                {{ tag.name }}
                            </MediaTag>
                        </div>
                    </FolderInfoRow>

                    <details v-if="group.label === 'Series' && data.series?.description" class="group text-foreground-1 flex w-full flex-col open:gap-2">
                        <summary class="hover:text-foreground-0 text-foreground-1 flex w-fit cursor-pointer items-center gap-1.5 font-semibold uppercase transition-colors">
                            <ProiconsChevronRight class="-ms-1 size-3 transition-transform duration-200 group-open:rotate-90" />

                            Description
                        </summary>

                        <p class="scrollbar-minimal bg-surface-3 dark:bg-surface-2 overflow-x-auto rounded-lg p-3 leading-relaxed whitespace-pre-line">
                            {{ data.series.description }}
                        </p>
                    </details>
                </div>
            </div>

            <div v-if="index !== metadataItems.length - 1" class="border-foreground-0/10 border-t" />
        </template>

        <details class="group text-foreground-1 flex flex-col open:gap-2">
            <summary class="hover:text-foreground-0 flex w-fit cursor-pointer items-center gap-1.5 transition-colors">
                <ProiconsChevronRight class="-ms-1 size-3 transition-transform duration-200 group-open:rotate-90" />

                Raw Data
            </summary>

            <pre class="scrollbar-minimal bg-surface-3 dark:bg-surface-2 overflow-x-auto rounded-lg p-3 leading-relaxed whitespace-pre-wrap">{{
                JSON.stringify(
                    {
                        ...data,
                        videos: undefined,
                        series: {
                            ...data.series,
                            images: undefined,
                        },
                    },
                    null,
                    2,
                )
            }}</pre>
        </details>
    </FolderTab>
</template>
