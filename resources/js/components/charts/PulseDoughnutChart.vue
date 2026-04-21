<script setup lang="ts">
import type { ChartData } from 'chart.js';

import { defineAsyncComponent } from 'vue';

const AsyncDoughnutChart = defineAsyncComponent(async () => {
    const { Chart, registerables } = await import('chart.js');
    const { Doughnut } = await import('vue-chartjs');

    Chart.register(...registerables);
    return Doughnut;
});

const props = defineProps<{
    chartData?: ChartData<'doughnut', number[], unknown>; // Strict typing
    chartOptions?: any;
}>();

const defaultChartData = {
    labels: ['Used', 'Free'],
    datasets: [
        {
            data: [20, 128],
            backgroundColor: ['#9333ea', '#c084fc30'],
            hoverBackgroundColor: ['#9333ea', '#c084fc30'],
        },
    ],
};
const defaultChartOptions = {
    borderWidth: 0,
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            enabled: false,
            callbacks: {
                label: (context: any) => context.formattedValue + ' MB',
            },
            displayColors: false,
        },
    },
};
</script>
<template>
    <div>
        <Suspense>
            <AsyncDoughnutChart :data="chartData ?? defaultChartData" :options="defaultChartOptions" />
            <template #fallback>
                <div class="suspense block size-full rounded-md shadow-xs ring-1 ring-gray-900/5"></div>
            </template>
        </Suspense>
    </div>
</template>
