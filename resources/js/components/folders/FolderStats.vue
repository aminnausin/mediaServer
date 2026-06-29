<script setup lang="ts">
import { computed, nextTick, useTemplateRef, watch } from 'vue';
import { useSeriesSizeHistory } from '@/service/folder/queries.ts';
import { Chart, registerables } from 'chart.js';
import { useContentStore } from '@/stores/ContentStore';
import { formatFileSize } from '@/service/util';
import { storeToRefs } from 'pinia';

import FolderTab from '@/components/folders/FolderTab.vue';

const { stateFolder } = storeToRefs(useContentStore());

Chart.register(...registerables);
let chart: Chart | null = null;

const chartCanvas = useTemplateRef('size-chart');
const seriesId = computed(() => stateFolder.value.series?.id);

const { data: sizeHistory } = useSeriesSizeHistory(seriesId);

const buildChart = () => {
    if (!chartCanvas.value || !sizeHistory.value?.length) return;

    chart?.destroy();

    chart = new Chart(chartCanvas.value, {
        type: 'line',
        data: {
            labels: sizeHistory.value.map((h) => new Date(h.recorded_at).toLocaleDateString(undefined, { month: 'short', day: 'numeric' })),
            datasets: [
                {
                    label: 'Total Size',
                    data: sizeHistory.value.map((h) => h.total_bytes),
                    borderColor: 'rgb(147, 51, 234)',
                    backgroundColor: 'rgba(147, 51, 234, 0.1)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 3,
                },
                {
                    label: 'File Count',
                    data: sizeHistory.value.map((h) => h.file_count),
                    borderColor: 'rgb(99, 102, 241)',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    fill: false,
                    tension: 0.3,
                    pointRadius: 3,
                    yAxisID: 'y2',
                },
            ],
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: true },
                tooltip: {
                    callbacks: {
                        label: (ctx) => {
                            if (ctx.datasetIndex === 0) return ` ${formatFileSize(ctx.raw as number)}`;
                            return ` ${ctx.raw} files`;
                        },
                    },
                },
            },
            scales: {
                y: {
                    ticks: {
                        callback: (val) => formatFileSize(val as number),
                    },
                },
                y2: {
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    ticks: {
                        callback: (val) => `${val} files`,
                    },
                },
            },
        },
    });
};

watch(sizeHistory, () => {
    nextTick(buildChart);
});
</script>

<template>
    <FolderTab>
        <h3 class="text-sm font-medium">Folder Size History</h3>
        <div v-if="sizeHistory?.length" class="relative">
            <canvas ref="size-chart" />
        </div>
        <div v-else class="text-foreground-2 flex items-center justify-center py-12 text-xs">No history recorded yet.</div>
    </FolderTab>
</template>
