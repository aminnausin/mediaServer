<script setup lang="ts">
import type { FolderResource } from '@/contracts/media';

import { formatFileSize, toFormattedDuration } from '@/service/util';
import { inject, useTemplateRef } from 'vue';
import { useScrollbarDetection } from '@/composables/design/useScrollbarDetection';
import { cn } from '@aminnausin/cedar-ui';

import FolderInfoRow from '@/components/folders/FolderInfoRow.vue';
import FolderTab from '@/components/folders/FolderTab.vue';

const folderInfoScrollContainer = useTemplateRef('folder-info');
const data = inject<FolderResource>('data');

const { hasScrollbar: folderInfoHasScrollbar } = useScrollbarDetection(folderInfoScrollContainer, 0, 'x');
</script>

<template>
    <FolderTab class="w-full @[40rem]:max-w-40">
        <div v-if="data" :class="cn('scrollbar-minimal flex gap-x-4 gap-y-2 overflow-x-auto @[40rem]:flex-col', { 'pb-2': folderInfoHasScrollbar })" ref="folder-info">
            <FolderInfoRow v-if="data.total_size" title="Total Size" :value="formatFileSize(data.total_size)" />
            <FolderInfoRow title="Total Views" :value="data.videos.reduce((acc, vid) => (acc += vid.view_count ?? 0), 0)" />
            <FolderInfoRow v-if="data.series?.episodes" title="Episodes" :value="data.series.episodes" />
            <FolderInfoRow v-if="data.series?.seasons" title="Seasons" :value="data.series?.seasons" />
            <FolderInfoRow title="Avg Duration" :value="toFormattedDuration(data.videos.reduce((acc, vid) => (acc += vid.duration ?? 0), 0) / (data.episodes || 1))" />
            <FolderInfoRow title="Intro Duration" :value="`${data.series?.avg_intro_duration}s`" />
            <FolderInfoRow v-if="data.series?.started_at" title="Start Date" :value="data.series?.started_at" />
            <FolderInfoRow v-if="data.series?.ended_at" title="End Date" :value="data.series?.ended_at" />
            <FolderInfoRow v-if="data.series?.rating" title="Average Score" :value="`${data.series?.rating}%`" />
            <FolderInfoRow v-if="data.series?.studio" title="Studio" :value="data.series?.studio" />
            <FolderInfoRow v-if="data.series?.folder_tags?.length" title="Tags">
                <div class="flex gap-0.5 *:w-fit *:max-w-full *:truncate @[40rem]:flex-col">
                    <div class="text-foreground-1 text-xs" :title="tag.name" v-for="tag in data.series?.folder_tags" :key="tag.id">{{ tag.name }}</div>
                </div>
            </FolderInfoRow>
        </div>
    </FolderTab>
</template>
