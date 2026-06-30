<script setup lang="ts">
import type { ChartOptions } from 'chart.js';

import { useSeriesSizeHistory } from '@/service/folder/queries.ts';
import { useContentStore } from '@/stores/ContentStore';
import { formatFileSize } from '@/service/util';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { computed } from 'vue';
import { cn } from '@aminnausin/cedar-ui';

import PulseLineChart from '@/components/charts/PulseLineChart.vue';
import FolderTab from '@/components/folders/FolderTab.vue';

const { stateFolder } = storeToRefs(useContentStore());
const { lightMode } = storeToRefs(useAppStore());

const seriesId = computed(() => stateFolder.value.series?.id);

const { data: sizeHistory, isLoading } = useSeriesSizeHistory(seriesId);

const chartData = computed(() => {
    const style = getComputedStyle(document.documentElement);
    const colourFileSize = style.getPropertyValue(lightMode.value ? '--color-primary' : '--color-primary-muted').trim();
    const colourFileCount = style.getPropertyValue('--color-warning-2').trim();

    return {
        labels: sizeHistory.value?.map((h) => new Date(h.recorded_at).toLocaleDateString(undefined, { month: 'short', day: 'numeric' })) ?? [],
        datasets: [
            {
                label: 'Size',
                data: sizeHistory.value?.map((h) => h.total_bytes) ?? [],
                borderColor: colourFileSize,
                borderWidth: 1.5,
                backgroundColor: `color-mix(in oklab, ${colourFileSize} 10%, transparent)`,
                fill: true,
                tension: 0.4,
                pointRadius: 1,
                pointHoverRadius: 3,
                yAxisID: 'y',
            },
            {
                label: 'Files',
                data: sizeHistory.value?.map((h) => h.file_count) ?? [],
                borderColor: colourFileCount,
                borderWidth: 1.5,
                backgroundColor: 'transparent',
                fill: false,
                tension: 0.4,
                pointRadius: 1,
                pointHoverRadius: 3,
                yAxisID: 'y2',
            },
        ],
    };
});

const chartOptions = computed<ChartOptions>(() => {
    const style = getComputedStyle(document.documentElement);
    const colourMuted = style.getPropertyValue('--color-foreground-2').trim() || '#9ca3af';
    const colourGrid = style.getPropertyValue('--color-foreground-3').trim();
    const colourFileSize = style.getPropertyValue(lightMode.value ? '--color-primary' : '--color-primary-muted').trim();
    const colourFileCount = style.getPropertyValue('--color-warning-2').trim();
    return {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        scales: {
            x: {
                border: { display: false },
                grid: { display: false },
                ticks: {
                    color: colourMuted,
                    maxRotation: 0,
                    maxTicksLimit: 8,
                    font: { size: 10 },
                },
            },
            y: {
                position: 'left',
                border: { display: false, dash: [2, 4] },
                grid: { color: `color-mix(in oklab, ${colourGrid} 60%, transparent)` },
                ticks: {
                    color: colourMuted,
                    maxTicksLimit: 3,
                    font: { size: 10 },
                    callback: (val) => formatFileSize(val as number),
                },
            },
            y2: {
                position: 'right',
                border: { display: false },
                grid: { drawOnChartArea: false },
                ticks: {
                    color: colourFileCount,
                    maxTicksLimit: 3,
                    font: { size: 10 },
                    callback: (val) => `${val}`,
                },
            },
        },
        plugins: {
            legend: { display: false },
            tooltip: {
                mode: 'index',
                position: 'nearest',
                intersect: false,
                bodyFont: {
                    size: 10,
                },
                callbacks: {
                    title: () => '',
                    label: (ctx) => {
                        if (ctx.datasetIndex === 0) return `  Size: ${formatFileSize(ctx.raw as number)}`;
                        return `  Files: ${ctx.raw}`;
                    },
                },
            },
        },
    };
});
</script>

<template>
    <FolderTab>
        <div class="flex items-center justify-between gap-4">
            <div class="flex flex-col gap-0.5">
                <h3 class="text-sm font-medium">Size History</h3>
                <div v-if="isLoading" class="bg-foreground-2/20 h-3 w-20 animate-pulse rounded"></div>
                <p v-else class="text-foreground-2 text-xs">{{ sizeHistory?.length ?? 0 }} snapshots</p>
            </div>
            <div class="flex items-center gap-3 text-xs">
                <span class="flex items-center gap-1.5">
                    <span class="bg-primary inline-block h-px w-4 rounded-full" />
                    Size
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="bg-warning-2 inline-block h-px w-4 rounded-full" />
                    Files
                </span>
            </div>
        </div>

        <div v-if="sizeHistory?.length && !isLoading" class="dark:bg-surface-2 relative h-48 rounded bg-white p-3 shadow-sm">
            <PulseLineChart
                :chart-data="chartData"
                :chart-options="chartOptions"
                class="shadow-none"
                :fallback-class="'bg-white p-3 dark:bg-neutral-900/70 h-full animate-pulse rounded'"
            />
        </div>
        <div v-else :class="cn('text-foreground-2 flex flex-col items-center justify-center gap-2 py-12 text-xs', { 'animate-pulse': isLoading })">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-6 opacity-30" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.5l4-4 4 4 4-6 4 4" />
            </svg>
            {{ isLoading ? 'Loading history...' : ' No history recorded yet' }}
        </div>
    </FolderTab>
</template>
