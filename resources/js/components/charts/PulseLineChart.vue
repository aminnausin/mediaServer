<script setup lang="ts">
import type { ChartData, Point } from 'chart.js';

import { defineAsyncComponent } from 'vue';

const props = defineProps<{
    chartData?: ChartData<'line', (number | Point | null)[], unknown>;
    chartOptions?: any;
}>();

const AsyncLineChart = defineAsyncComponent({
    loader: async () => {
        const { Chart, registerables } = await import('chart.js');
        const { Line } = await import('vue-chartjs');

        Chart.register(...registerables);
        return Line;
    },
    delay: 200,
});

const defaultChartData = {
    labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
    datasets: [
        {
            label: 'Data One',
            backgroundColor: '#f87979',
            data: [40, 39, 10, 40, 39, 80, 40],
        },
    ],
};

const defaultChartOptions = {
    maintainAspectRatio: false,
    layout: {
        autoPadding: false,
    },
    scales: {
        x: {
            display: false,
            grid: {
                display: false,
            },
        },
        y: {
            display: false,
            min: 0,
            max: 100,
            grid: {
                display: false,
            },
        },
    },
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            mode: 'index',
            position: 'nearest',
            intersect: false,
            callbacks: {
                title: () => '',
                label: (context: any) => `${context.label} - ${context.formattedValue}%`,
            },
            displayColors: false,
        },
    },
};
</script>

<template>
    <Suspense>
        <AsyncLineChart
            class="dark:bg-primary-dark-800 w-full rounded-md bg-white shadow-xs ring-1 ring-gray-900/5"
            v-bind="$attrs"
            :data="chartData ?? defaultChartData"
            :options="chartOptions ?? defaultChartOptions"
        />
        <template #fallback>
            <div v-bind="$attrs" class="suspense block size-full rounded-md shadow-xs ring-1 ring-gray-900/5"></div>
        </template>
    </Suspense>
</template>
