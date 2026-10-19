<script setup lang="ts">
import type { DiskCategory, DiskLocation } from '@/contracts/server';

import { useGetDiskUsage, useRefreshDiskUsage } from '@/service/server/queries';
import { formatFileSize } from '@/service/util';
import { ButtonBase } from '@/components/cedar-ui/button';
import { computed } from 'vue';

import TranscriptStatus from '@/components/video/transcript/TranscriptStatus.vue';
import DashboardCard from '@/components/cards/layout/DashboardCard.vue';

import ProiconsAlertTriangle from '~icons/proicons/alert-triangle';
import ProiconsArrowSync from '~icons/proicons/arrow-sync';
import ProiconsDatabase from '~icons/proicons/database';
import ProiconsLibrary from '~icons/proicons/library';
import CircumServer from '~icons/circum/server';
import IconServer from '@/components/icons/IconServer.vue';

const panel = 'data-card pulse-card flex gap-3 flex-col';
const skeleton = 'animate-pulse motion-reduce:animate-none bg-neutral-200 dark:bg-neutral-700';

const categories: Record<DiskCategory, { label: string; color: string }> = {
    library: { label: 'Libraries', color: 'bg-primary' },
    images: { label: 'Images', color: 'bg-primary' },
    subtitles: { label: 'Subtitles', color: 'bg-rose-500' },
    cache: { label: 'Cache', color: 'bg-blue-500' },
    logs: { label: 'Logs', color: 'bg-slate-400' },
    legacy: { label: 'Legacy', color: 'bg-yellow-500' },
};

const categoryOrder = Object.keys(categories) as DiskCategory[];
const appCategories = categoryOrder.filter((c) => c !== 'library');

const { data, isPending, isError, refetch } = useGetDiskUsage();
const { mutate: refresh, isPending: refreshing } = useRefreshDiskUsage();

const panelClass = computed(() => [panel, refreshing.value && 'opacity-60 transition-opacity']);
const sized = computed(() => (data.value?.locations ?? []).filter((l) => l.bytes).sort((a, b) => b.bytes! - a.bytes!));

const sumGroupSize = (items: DiskLocation[]) => items.reduce((total, l) => total + (l.bytes ?? 0), 0);

// Scales app data categories by total size of app data rather than by disk
const appData = computed(() => {
    const groups = appCategories
        .map((key) => {
            const items = sized.value.filter((l) => l.category === key);
            return { key, ...categories[key], items, bytes: sumGroupSize(items) };
        })
        .filter((g) => g.bytes > 0)
        .sort((a, b) => b.bytes - a.bytes);

    const total = groups.reduce((t, g) => t + g.bytes, 0);

    return { groups, total, segments: groups.map((g) => ({ ...g, percent: (g.bytes / total) * 100 })) };
});
const libraries = computed(() => {
    const items = sized.value.filter((l) => l.category === 'library');
    return { items, total: sumGroupSize(items) };
});

const disks = computed(() =>
    (data.value?.filesystems ?? []).map((fs, index, all) => {
        const names = categoryOrder.filter((c) => data.value!.locations.some((l) => l.filesystem === fs.id && l.category === c)).map((c) => categories[c].label);
        const percentUsed = fs.totalSpace ? Math.round((fs.usedSpace / fs.totalSpace) * 100) : 0;

        return {
            ...fs,
            title: all.length > 1 ? `Disk ${index + 1}` : 'Disk',
            names: names.join(' · '),
            percentUsed,
            color: percentUsed >= 90 ? 'bg-danger-2' : percentUsed >= 75 ? 'bg-warning-2' : 'bg-success',
        };
    }),
);
</script>

<template>
    <DashboardCard cols="2" :rows="2" name="Storage" title="Disk usage">
        <template #icon>
            <IconServer class="h-6 w-6" />
        </template>
        <template #actions>
            <ButtonBase
                type="button"
                class="text-foreground-2 hover:text-foreground-1 ml-auto disabled:opacity-50"
                title="Recalculate sizes"
                aria-label="Recalculate sizes"
                :disabled="isPending || refreshing"
                :use-size="false"
                @click="refresh()"
            >
                <ProiconsArrowSync :class="['h-4 w-4', { 'animate-spin': refreshing }]" />
            </ButtonBase>
        </template>

        <TranscriptStatus
            v-if="isError && !data"
            :is-busy="refreshing"
            :copy="{ heading: 'Failed to load disk usage' }"
            badge-class="bg-danger-2/10 text-danger-2"
            @action="refetch()"
        >
            <template #icon>
                <ProiconsAlertTriangle class="size-4 sm:size-5" />
            </template>
            <template #buttonIcon>
                <ProiconsArrowSync class="size-3.5" />
            </template>
        </TranscriptStatus>

        <template v-else-if="isPending">
            <section :class="panel" aria-busy="true">
                <div class="flex items-center gap-2 text-base">
                    <ProiconsDatabase class="text-foreground-2 size-4" />
                    <h3 class="text-foreground-2">Disks</h3>
                </div>

                <div class="flex flex-col gap-1.5">
                    <div class="flex items-baseline gap-2">
                        <span :class="[skeleton, 'h-4 w-12 rounded']"></span>
                        <span :class="[skeleton, 'ml-auto h-4 w-28 rounded']"></span>
                    </div>
                    <div :class="[skeleton, 'h-1.5 w-full rounded-full']"></div>
                </div>
            </section>

            <section :class="panel" aria-busy="true">
                <div class="flex items-center gap-2 text-base">
                    <CircumServer class="text-foreground-2 size-5" />
                    <h3 class="text-foreground-2">App data</h3>
                    <span :class="[skeleton, 'ml-auto h-4 w-14 rounded']"></span>
                </div>

                <div :class="[skeleton, 'h-2 w-full rounded-full']"></div>
                <ul class="flex flex-col gap-2">
                    <li v-for="n in 4" :key="n" class="flex items-center gap-2">
                        <span :class="[skeleton, 'h-2 w-2 shrink-0 rounded-full']"></span>
                        <span :class="[skeleton, 'h-4 w-20 rounded']"></span>
                        <span :class="[skeleton, 'ml-auto h-4 w-12 rounded']"></span>
                    </li>
                </ul>
            </section>

            <section :class="panel" aria-busy="true">
                <div class="flex items-center gap-2 text-base">
                    <ProiconsLibrary class="size-4.5" />
                    <h3 class="text-foreground-0">Libraries</h3>
                    <span :class="[skeleton, 'ml-auto h-4 w-14 rounded']"></span>
                </div>

                <ul class="flex flex-col gap-1.5">
                    <li v-for="n in 2" :key="n" class="flex items-center gap-2">
                        <span :class="[skeleton, 'h-2 w-2 shrink-0 rounded-full']"></span>
                        <span :class="[skeleton, 'h-4 w-24 rounded']"></span>
                        <span :class="[skeleton, 'ml-auto h-4 w-12 rounded']"></span>
                    </li>
                </ul>
            </section>
        </template>

        <template v-else>
            <section :class="panelClass">
                <div class="flex items-center gap-2 text-base">
                    <ProiconsDatabase class="text-foreground-2 size-4" />
                    <h3 class="text-foreground-2">Disks</h3>
                </div>

                <div v-for="disk in disks" :key="disk.id" class="flex flex-col gap-1.5">
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-sm">{{ disk.title }}</h3>
                        <span class="text-foreground-2 truncate text-xs">{{ disk.names }}</span>
                        <span class="ml-auto shrink-0 text-sm">
                            {{ formatFileSize(disk.freeSpace) }} free
                            <span class="text-foreground-2 text-xs">of {{ formatFileSize(disk.totalSpace) }}</span>
                        </span>
                    </div>
                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-neutral-200 dark:bg-neutral-700" role="img" :aria-label="`${disk.percentUsed}% used`">
                        <div :class="disk.color" class="h-full" :style="{ width: `${disk.percentUsed}%` }"></div>
                    </div>
                </div>
            </section>

            <section v-if="appData.total" :class="panelClass">
                <div class="flex items-center gap-2 text-base">
                    <CircumServer class="text-foreground-2 size-5" />
                    <h3 class="text-foreground-2">App data</h3>
                    <span class="ml-auto">{{ formatFileSize(appData.total) }}</span>
                </div>

                <div class="flex h-4 w-full gap-px overflow-hidden rounded-sm">
                    <div v-for="segment in appData.segments" :key="segment.key" :class="segment.color" class="min-w-0.5" :style="{ width: `${segment.percent}%` }"></div>
                </div>

                <ul class="flex flex-col gap-2 text-sm">
                    <li v-for="group in appData.groups" :key="group.key">
                        <div class="flex items-center gap-2">
                            <span :class="group.color" class="h-2 w-2 shrink-0 rounded-full"></span>
                            <span>{{ group.label }}</span>
                            <span class="ml-auto">{{ formatFileSize(group.bytes) }}</span>
                        </div>
                        <ul v-if="group.items.length > 1" class="text-foreground-2 mt-1 flex flex-col gap-0.5 pl-4 text-xs">
                            <li v-for="item in group.items" :key="item.key" class="flex items-center gap-2">
                                <span class="truncate" :title="item.path">{{ item.label }}</span>
                                <span class="ml-auto">{{ formatFileSize(item.bytes!) }}</span>
                            </li>
                        </ul>
                    </li>
                </ul>
            </section>

            <section v-if="libraries.total" :class="[panelClass, 'text-foreground-2']">
                <div class="flex items-center gap-2 text-base">
                    <ProiconsLibrary class="size-4.5" />
                    <h3 class="text-foreground-0">Libraries</h3>
                    <span class="text-foreground-0 ml-auto">{{ formatFileSize(libraries.total) }}</span>
                </div>

                <ul class="flex flex-col gap-1.5 text-sm">
                    <li v-for="library in libraries.items" :key="library.key" class="flex items-center gap-2">
                        <span :class="categories.library.color" class="h-2 w-2 shrink-0 rounded-full"></span>
                        <span class="truncate" :title="library.path">{{ library.label }}</span>
                        <span class="ml-auto">{{ formatFileSize(library.bytes!) }}</span>
                    </li>
                </ul>
            </section>
        </template>
    </DashboardCard>
</template>
