<script setup lang="ts">
import type { FolderResource } from '@/contracts/media';
import type { Ref } from 'vue';

import { computed, inject, nextTick, onMounted, ref, useTemplateRef, watch } from 'vue';
import { formatFileSize, toFormattedDate, toFormattedDuration } from '@/service/util';
import { ButtonBase } from '@/components/cedar-ui/button';
import { cn } from '@aminnausin/cedar-ui';

import FolderTab from '@/components/folders/FolderTab.vue';
import MediaTag from '@/components/labels/MediaTag.vue';

const data = inject<Ref<FolderResource>>('data');
const isAudio = inject<Ref<boolean>>('isAudio');

const avgDuration = computed(() => (data?.value.file_count ? data.value.videos.reduce((acc, vid) => (acc += vid.duration ?? 0), 0) / data.value.file_count : undefined));
const startingSeason = computed(() => (data?.value.series?.started_at ? getMediaReleaseSeason(data.value.series.started_at) : undefined));
const endingSeason = computed(() => (data?.value.series?.ended_at ? getMediaReleaseSeason(data.value.series.ended_at) : data?.value.series?.started_at ? 'Ongoing' : undefined));
const folderDates = computed(() =>
    !startingSeason.value ? toFormattedDate(data?.value.series?.created_at, false, { year: 'numeric', month: '2-digit', day: '2-digit' }) : undefined,
);

const stats = computed(
    () =>
        [
            { label: isAudio?.value ? 'Discs' : 'Seasons', value: data?.value.series?.seasons },
            { label: isAudio?.value ? 'Tracks' : 'Episodes', value: data?.value.series?.episodes },
            !isAudio?.value && data?.value.series?.films && { label: 'Films', value: data?.value.series?.films },
            !isAudio?.value &&
                !data?.value.series?.films &&
                data?.value.total_size && { label: 'Avg Size', value: formatFileSize(data?.value.total_size / (data?.value.file_count ?? 1)) },
            { label: 'Avg Duration', value: avgDuration.value ? toFormattedDuration(avgDuration.value, false) : '—' },
        ].filter(Boolean) as { label: string; value: any }[],
);

const descriptionRef = useTemplateRef('folder-description');

const isOverflowing = ref(false);
const isExpanded = ref(false);

function getMediaReleaseSeason(dateString?: string): string | null {
    if (!dateString) {
        return null;
    }

    const date = new Date(dateString);
    const month = date.getMonth();
    const year = date.getFullYear();
    switch (month) {
        case 1:
        case 2:
        case 3:
            return `Winter ${year}`;
        case 4:
        case 5:
        case 6:
            return `Spring ${year}`;
        case 7:
        case 8:
        case 9:
            return `Summer ${year}`;
        default:
            return `Fall ${year}`;
    }
}

function checkOverflow() {
    if (!descriptionRef.value) return;

    isOverflowing.value = descriptionRef.value.scrollHeight > descriptionRef.value.clientHeight;
}

watch([() => data?.value.series?.description, () => isExpanded.value], (values, oldValues) => {
    nextTick(() => checkOverflow());
});

onMounted(() => {
    checkOverflow();
});
</script>

<template>
    <FolderTab v-if="data && data.series" class="text-xs">
        <div class="flex flex-col gap-1 overflow-clip">
            <div class="flex flex-wrap justify-between gap-x-2 gap-y-1">
                <div class="flex flex-1 flex-col gap-1">
                    <h3 class="text-xl font-semibold capitalize">{{ data.series?.title ?? data.title }}</h3>
                    <p class="text-foreground-1 text-sm" v-if="data.series?.studio">{{ data.series.studio }} · {{ data.path?.split('/')?.[0] }}</p>
                </div>

                <div class="flex flex-col items-end gap-1 tabular-nums" v-if="data.series">
                    <span class="text-primary dark:text-primary-muted text-xl font-bold">
                        {{ data.series.rating }}<span class="text-foreground-2 text-sm font-normal">{{ data.series.rating !== null ? '/100' : 'No Rating' }}</span>
                    </span>
                    <p class="text-foreground-2 h-4">
                        <template v-if="startingSeason">
                            <span>{{ startingSeason }}</span>
                            <span v-if="endingSeason && endingSeason !== startingSeason"> – {{ endingSeason }}</span>
                        </template>
                        <span v-else-if="folderDates">{{ folderDates }}</span>

                        <!-- {{ toFormattedDate(data.series.started_at, false, { year: 'numeric', month: '2-digit', day: '2-digit' }) }} – -->
                        <!-- {{ data.series.ended_at ? toFormattedDate(data.series.ended_at, false, { year: 'numeric', month: '2-digit', day: '2-digit' }) : 'Ongoing' }} -->
                    </p>
                </div>
            </div>

            <div class="flex w-full flex-1 flex-wrap gap-1" v-if="data.series?.folder_tags?.length">
                <MediaTag
                    v-for="tag in data.series.folder_tags.slice(0, Math.min(5, data.series.folder_tags.length))"
                    :key="tag.id"
                    class="bg-surface-3! text-foreground-0! py-0.5 text-xs capitalize"
                >
                    {{ tag.name }}
                </MediaTag>
            </div>
        </div>

        <article :class="['text-foreground-1 flex w-full flex-1 flex-col justify-between gap-1 text-sm leading-relaxed text-balance', { 'max-h-32': !isExpanded }]">
            <div
                :class="[
                    'flex-1 overflow-hidden whitespace-pre-wrap',
                    { 'h-20': !isExpanded && isOverflowing }, // h-16 and 2.5rem on big screens if show more button exists and not expanded
                ]"
                ref="folder-description"
                id="folder-description"
            >
                {{ data.series?.description ?? 'No Description' }}
            </div>
            <ButtonBase
                v-if="isOverflowing || isExpanded"
                @click="isExpanded = !isExpanded"
                :class="['hocus:text-foreground-0 block h-auto w-fit p-0 transition-colors', { 'leading-none': !isExpanded }]"
                :title="isExpanded ? 'Hide expanded description' : 'Show expanded description'"
                :aria-expanded="isExpanded"
                aria-controls="folder-description"
            >
                {{ isExpanded ? 'Show less' : '...more' }}
            </ButtonBase>
        </article>

        <div :class="cn('grid grid-cols-1 gap-2', { '@3xs:grid-cols-3': stats.length >= 3 }, { '@3xs:grid-cols-2 @sm:grid-cols-4': stats.length >= 4 })" v-if="data.series">
            <div class="bg-surface-3 flex flex-col items-center justify-center gap-0.5 rounded-lg p-3 text-center" v-for="stat in stats" :key="stat.label">
                <span class="text-base font-semibold">{{ stat.value ?? '—' }}</span>
                <span class="text-foreground-2">{{ stat.label }}</span>
            </div>
        </div>
    </FolderTab>
</template>
