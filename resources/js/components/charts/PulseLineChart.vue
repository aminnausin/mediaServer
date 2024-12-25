<script setup lang="ts">
import { Chart, registerables } from 'chart.js';
import { Line } from 'vue-chartjs';

Chart.register(...registerables);

const props = defineProps<{
    class?: string;
    chartData?: any;
    chartOptions?: any;
}>();

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
    <Line
        :class="`${props.class} w-full ring-1 ring-gray-900/5 bg-white dark:bg-primary-dark-800 rounded-md shadow-sm`"
        :data="props.chartData ?? defaultChartData"
        :options="props.chartOptions ?? defaultChartOptions"
    ></Line>
</template>
