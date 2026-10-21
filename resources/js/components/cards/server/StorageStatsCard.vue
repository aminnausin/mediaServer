<script setup lang="ts">
import type { DiskCategory, DiskLocation } from '@/contracts/server';

import { useGetDiskUsage, useRefreshDiskUsage } from '@/service/server/queries';
import { formatFileSize } from '@/service/util';
import { ButtonBase } from '@/components/cedar-ui/button';
import { computed } from 'vue';
import { cn } from '@aminnausin/cedar-ui';

import TranscriptStatus from '@/components/video/transcript/TranscriptStatus.vue';
import DiskUsageCard from '@/components/cards/server/storage/DiskUsageCard.vue';
import DashboardCard from '@/components/cards/layout/DashboardCard.vue';

import ProiconsAlertTriangle from '~icons/proicons/alert-triangle';
import ProiconsArrowSync from '~icons/proicons/arrow-sync';
import ProiconsDatabase from '~icons/proicons/database';
import ProiconsLibrary from '~icons/proicons/library';
import CircumServer from '~icons/circum/server';
import IconServer from '@/components/icons/IconServer.vue';

const panel = 'data-card pulse-card flex gap-3 flex-col';
const skeleton = 'animate-pulse motion-reduce:animate-none bg-neutral-200 dark:bg-neutral-700';

const categories: Record<DiskCategory, { label: string; colour: string }> = {
    library: { label: 'Libraries', colour: 'bg-primary-muted dark:bg-primary-active' },
    images: { label: 'Images', colour: 'bg-primary-muted/80 dark:bg-primary-muted' },
    subtitles: { label: 'Subtitles', colour: 'bg-rose-400 dark:bg-pink-500/80' },
    logs: { label: 'Logs', colour: 'bg-neutral-300 dark:bg-stone-300/80' },
    legacy: { label: 'Legacy', colour: 'bg-amber-300 dark:bg-amber-500/90' },
    cache: { label: 'Cache', colour: 'bg-pink-300 dark:bg-fuchsia-300/60' },
};

const diskKinds = {
    library: { label: 'Libraries', colour: 'bg-primary-muted dark:bg-primary-active' },
    app: { label: 'App data', colour: 'bg-cyan-500' },
    other: { label: 'Other', colour: 'bg-neutral-300 dark:bg-neutral-500/60' },
};

const categoryOrder = Object.keys(categories) as DiskCategory[];
const appCategories = categoryOrder.filter((c) => c !== 'library');

const { data, isPending, isError, refetch } = useGetDiskUsage();
const { mutate: refresh, isPending: refreshing } = useRefreshDiskUsage();

const panelClass = computed(() => [panel, refreshing.value && 'opacity-60 transition-opacity']);
const sized = computed(() => (data.value?.locations ?? []).filter((l) => l.bytes).sort((a, b) => b.bytes! - a.bytes!));

const sumGroupSize = (items: DiskLocation[]) => items.reduce((total, l) => total + (l.bytes ?? 0), 0);

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
    return { items, total: sumGroupSize(items), max: items[0]?.bytes ?? 0 };
});

const disks = computed(() =>
    (data.value?.filesystems ?? []).map((fs, index, all) => {
        const onDisk = (data.value?.locations ?? []).filter((l) => l.filesystem === fs.id).sort((a, b) => (b.bytes ?? 0) - (a.bytes ?? 0));
        const libs = onDisk.filter((l) => l.category === 'library');
        const appData = onDisk.filter((l) => l.category !== 'library');

        const libBytes = sumGroupSize(libs);
        const appBytes = sumGroupSize(appData);
        const otherBytes = Math.max(0, fs.usedSpace - libBytes - appBytes);

        const toPercent = (bytes: number) => (fs.totalSpace ? (bytes / fs.totalSpace) * 100 : 0);
        const percentUsed = Math.round(toPercent(fs.usedSpace));

        const segments = (
            [
                { key: 'library', bytes: libBytes },
                { key: 'app', bytes: appBytes },
                { key: 'other', bytes: otherBytes },
            ] as const
        )
            .filter((s) => s.bytes > 0)
            .map((s) => ({ ...s, ...diskKinds[s.key], percent: toPercent(s.bytes) }));

        const top = libs.slice(0, 3).map((l) => l.label);
        const rest = libs.length - top.length;

        const getDiskColour = (percentUsed: number) => {
            if (percentUsed >= 90) return 'bg-danger-2';
            if (percentUsed >= 75) return 'bg-warning-2';
            return 'bg-success';
        };

        return {
            ...fs,
            title: all.length > 1 ? `Disk ${index + 1}` : 'Disk',
            percentUsed,
            segments,
            contents: [appData.length > 0 && 'app data', ...top, rest > 0 ? `+${rest} more` : ''].filter(Boolean).join(' · '),
            libraryNames: libs.map((l) => l.label).join(', '),
            colour: getDiskColour(percentUsed),
        };
    }),
);
</script>

<template>
    <DashboardCard cols="2" :rows="2" name="Storage" title="Disk usage" header-class="2xl:min-h-10 2xl:mb-0">
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
                <DiskUsageCard :version="3" :disks="disks" :disk-kinds="diskKinds"></DiskUsageCard>
            </section>

            <section v-if="appData.total" :class="panelClass">
                <div class="flex items-center gap-2 text-base">
                    <CircumServer class="text-foreground-2 size-5" />
                    <h3 class="text-foreground-2">App data</h3>
                    <span class="ml-auto">{{ formatFileSize(appData.total) }}</span>
                </div>

                <div class="flex h-1.5 w-full gap-px overflow-hidden rounded-sm">
                    <div
                        v-for="segment in appData.segments"
                        :key="segment.key"
                        :class="
                            cn(segment.colour, 'flex min-w-6 items-center ps-2 text-center text-xs text-white tabular-nums', {
                                'text-foreground-i dark': segment.label === 'Legacy',
                            })
                        "
                        :style="{ width: `${segment.percent}%` }"
                    ></div>
                </div>

                <ul class="grid grid-cols-1 gap-3 lg:grid-cols-2">
                    <li v-for="group in appData.groups" :key="group.key">
                        <div class="flex items-center gap-2">
                            <span :class="group.colour" class="h-2 w-2 shrink-0 rounded-full"></span>
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
                    <h3>Libraries</h3>
                    <span class="text-foreground-0 ml-auto">{{ formatFileSize(libraries.total) }}</span>
                </div>

                <ul class="grid grid-cols-2 gap-x-4 gap-y-3 sm:grid-cols-4">
                    <li v-for="library in libraries.items" :key="library.key" class="flex min-w-0 flex-col gap-1">
                        <RouterLink :to="`/${library.label}`">
                            <span class="text-foreground-0 hover:text-primary cursor-pointer truncate" :title="library.path">{{ library.label }}</span>
                        </RouterLink>
                        <span class="text-xs">{{ formatFileSize(library.bytes!) }}</span>
                        <div class="h-1 w-full overflow-hidden rounded-full bg-neutral-200 dark:bg-neutral-700">
                            <div :class="categories.library.colour" class="h-full" :style="{ width: `${(library.bytes! / libraries.max) * 100}%` }"></div>
                        </div>
                    </li>
                </ul>
            </section>
        </template>
    </DashboardCard>
</template>
