<script setup lang="ts">
import { formatFileSize, toFormattedDate, toFormattedDuration, toTimeSpan } from '@/service/util';
import { computed, useTemplateRef } from 'vue';
import { useScrollbarDetection } from '@/composables/design/useScrollbarDetection';
import { useContentStore } from '@/stores/ContentStore';
import { storeToRefs } from 'pinia';
import { cn } from '@aminnausin/cedar-ui';

import FolderInfoRow from '@/components/folders/FolderInfoRow.vue';
import FolderTab from '@/components/folders/FolderTab.vue';

const folderDatesScrollContainer = useTemplateRef('folder-dates');
const folderInfoScrollContainer = useTemplateRef('folder-info');

const { hasScrollbar: folderDatesHasScrollbar } = useScrollbarDetection(folderDatesScrollContainer, 0, 'x');
const { hasScrollbar: folderInfoHasScrollbar } = useScrollbarDetection(folderInfoScrollContainer, 0, 'x');
const { stateFolder: stateFolder } = storeToRefs(useContentStore());

const watchProgress = computed(() => `${stateFolder.value.videos.reduce((acc, vid) => (acc += vid.completion_count ? 1 : 0), 0)}/${stateFolder.value.file_count}`);
const avgDuration = computed(() => toFormattedDuration(stateFolder.value.videos.reduce((acc, vid) => acc + (vid.duration ?? 0), 0) / (stateFolder.value.file_count || 1)));
const totalViews = computed(() => stateFolder?.value.videos.reduce((acc, vid) => acc + (vid.view_count ?? 0), 0) ?? 0);
</script>

<template>
    <div class="flex w-full flex-col gap-2 @[40rem]:max-w-40">
        <FolderTab>
            <div :class="cn('scrollbar-minimal flex gap-x-4 gap-y-2 overflow-x-auto @[40rem]:flex-col', { 'pb-2': folderInfoHasScrollbar })" ref="folder-info">
                <!-- Personal -->
                <FolderInfoRow :title="'Progress'" :tooltip="`You have completed ${watchProgress} files`" :value="watchProgress" />
                <FolderInfoRow title="Views" :value="totalViews" />

                <!-- Files -->
                <template v-if="stateFolder.series?.episodes">
                    <FolderInfoRow v-if="stateFolder.series?.seasons" title="Seasons" :value="stateFolder.series?.seasons" />
                    <FolderInfoRow
                        title="Episodes"
                        :value="stateFolder.file_count < stateFolder.series.episodes ? `${stateFolder.file_count}/${stateFolder.series?.episodes}` : stateFolder.series.episodes"
                        :tooltip="stateFolder.file_count < stateFolder.series.episodes ? `${stateFolder.series?.episodes - stateFolder.file_count} files missing` : undefined"
                    />
                </template>
                <FolderInfoRow v-else title="Files" :value="stateFolder.file_count" />

                <FolderInfoRow title="Total Size" :value="formatFileSize(stateFolder.total_size)" />
                <FolderInfoRow title="Avg Duration" :value="avgDuration" />
                <FolderInfoRow title="Intro Duration" :value="`${stateFolder.series?.avg_intro_duration}s`" />

                <!-- Metadata -->
                <FolderInfoRow v-if="stateFolder.series?.rating !== null" title="Average Score" :value="`${stateFolder.series?.rating}%`" />
                <FolderInfoRow v-if="stateFolder.series?.studio" title="Studios" :value="stateFolder.series?.studio" />
                <FolderInfoRow v-if="stateFolder.series?.started_at" title="Start Date" :value="stateFolder.series?.started_at" />
                <FolderInfoRow v-if="stateFolder.series?.ended_at" title="End Date" :value="stateFolder.series?.ended_at" />
                <FolderInfoRow v-if="stateFolder.series?.folder_tags?.length" title="Tags">
                    <div class="flex gap-0.5 *:w-fit *:max-w-full *:truncate @[40rem]:flex-col">
                        <div class="text-foreground-1 text-xs" :title="tag.name" v-for="tag in stateFolder.series?.folder_tags" :key="tag.id">{{ tag.name }}</div>
                    </div>
                </FolderInfoRow>

                <!-- Timestamps  -->
            </div>
        </FolderTab>
        <FolderTab>
            <div :class="cn('scrollbar-minimal flex gap-x-4 gap-y-2 overflow-x-auto @[40rem]:flex-col', { 'pb-2': folderDatesHasScrollbar })" ref="folder-dates">
                <FolderInfoRow
                    v-if="stateFolder.scanned_at"
                    title="First Scanned"
                    :value="toTimeSpan(stateFolder.scanned_at, '')"
                    :tooltip="toFormattedDate(stateFolder.scanned_at)"
                />
                <FolderInfoRow
                    v-if="stateFolder.series?.updated_at"
                    title="Last Updated"
                    :value="toTimeSpan(stateFolder.series?.updated_at, '')"
                    :tooltip="toFormattedDate(stateFolder.series?.updated_at)"
                />
                <FolderInfoRow
                    v-if="stateFolder.series?.edited_at"
                    title="Last Edited"
                    :value="toTimeSpan(stateFolder.series?.edited_at, '')"
                    :tooltip="toFormattedDate(stateFolder.series?.edited_at)"
                />
                <FolderInfoRow title="Downloads" :value="stateFolder.series?.downloads_enabled ? 'Enabled' : 'Disabled'" />
            </div>
        </FolderTab>
    </div>
</template>
