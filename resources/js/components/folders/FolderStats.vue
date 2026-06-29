<script setup lang="ts">
import { computed, nextTick, onMounted, useTemplateRef, watch } from 'vue';
import { useSeriesSizeHistory } from '@/service/folder/queries.ts';
import { Chart, registerables } from 'chart.js';
import { useContentStore } from '@/stores/ContentStore';
import { formatFileSize, toFormattedDate } from '@/service/util';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';

import FolderTab from '@/components/folders/FolderTab.vue';

const { stateFolder } = storeToRefs(useContentStore());
const { lightMode } = storeToRefs(useAppStore());

Chart.register(...registerables);
let chart: Chart | null = null;

const chartCanvas = useTemplateRef('size-chart');
const seriesId = computed(() => stateFolder.value.series?.id);

const { data: sizeHistory } = useSeriesSizeHistory(seriesId);

const buildChart = () => {
    if (!chartCanvas.value || !sizeHistory.value?.length) return;
    chart?.destroy();

    const style = getComputedStyle(document.documentElement);
    const colourMuted = style.getPropertyValue('--color-foreground-2').trim() || '#9ca3af';
    const colourGrid = style.getPropertyValue('--color-foreground-3').trim();
    const colourFileSize = style.getPropertyValue(lightMode ? '--color-primary' : '--color-primary-muted').trim();
    const colourFileCount = style.getPropertyValue('--color-warning-2').trim();

    chart = new Chart(chartCanvas.value, {
        type: 'line',
        data: {
            labels: sizeHistory.value.map((h) => new Date(h.recorded_at).toLocaleDateString(undefined, { month: 'short', day: 'numeric' })),
            datasets: [
                {
                    label: 'Size',
                    data: sizeHistory.value.map((h) => h.total_bytes),
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
                    data: sizeHistory.value.map((h) => h.file_count),
                    borderColor: colourFileCount,
                    borderWidth: 1.5,
                    backgroundColor: 'transparent',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 1,
                    pointHoverRadius: 3,
                    yAxisID: 'y2',
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
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
            scales: {
                x: {
                    border: { display: false },
                    grid: { display: false },
                    ticks: {
                        color: colourMuted,
                        font: { size: 10 },
                        maxRotation: 0,
                        maxTicksLimit: 5,
                    },
                },
                y: {
                    position: 'left',
                    border: { display: false, dash: [2, 4] },
                    grid: { color: `color-mix(in oklab, ${colourGrid} 60%, transparent)` },
                    ticks: {
                        color: colourMuted,
                        font: { size: 10 },
                        callback: (val) => formatFileSize(val as number),
                        maxTicksLimit: 3,
                    },
                },
                y2: {
                    position: 'right',
                    border: { display: false },
                    grid: { drawOnChartArea: false },
                    ticks: {
                        color: colourFileCount,
                        font: { size: 10 },
                        callback: (val) => `${val}`,
                        maxTicksLimit: 3,
                    },
                },
            },
        },
    });
};

watch([sizeHistory, lightMode], () => nextTick(buildChart));
onMounted(() => {
    if (sizeHistory.value?.length) nextTick(buildChart);
});
</script>

<template>
    <FolderTab>
        <div class="flex items-center justify-between gap-4">
            <div class="flex flex-col gap-0.5">
                <h3 class="text-sm font-medium">Size History</h3>
                <p class="text-foreground-2 text-xs">{{ sizeHistory?.length ?? 0 }} snapshots</p>
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

        <div v-if="sizeHistory?.length" class="relative rounded bg-white p-3 shadow-sm dark:bg-neutral-900/70">
            <canvas ref="size-chart" />
        </div>
        <div v-else class="text-foreground-2 flex flex-col items-center justify-center gap-2 py-12 text-xs">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-6 opacity-30" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.5l4-4 4 4 4-6 4 4" />
            </svg>
            No history recorded yet.
        </div>
    </FolderTab>
</template>
