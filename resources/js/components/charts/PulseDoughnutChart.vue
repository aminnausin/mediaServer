<script setup lang="ts">
import { Chart, registerables } from 'chart.js';
import { Doughnut } from 'vue-chartjs';

Chart.register(...registerables);

const props = defineProps<{
    chartData?: any;
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
    <Doughnut :data="props.chartData ?? defaultChartData" :options="props.chartOptions ?? defaultChartOptions"></Doughnut>
</template>
