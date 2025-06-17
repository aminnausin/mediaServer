<script setup lang="ts">
import type { PulseResponse, PulseServerResponse } from '@/types/types.ts';

import { format_number, friendlyFileSize } from '@/service/pulseUtil';
import { toTimeSpan } from '@/service/util';
import { ref, watch } from 'vue';

import PulseDoughnutChart from '@/components/charts/PulseDoughnutChart.vue';
import IconSignalSlash from '@/components/icons/IconSignalSlash.vue';
import PulseLineChart from '@/components/charts/PulseLineChart.vue';
import IconServer from '@/components/icons/IconServer.vue';

const props = withDefaults(
    defineProps<{
        cols?: number | string;
        rows?: number;
        class?: string;
        pulseData?: PulseResponse;
        isLoading?: boolean;
    }>(),
    {
        cols: 'full',
        rows: 1,
    },
);

const servers = ref<{ [key: string]: PulseServerResponse }>();

watch(
    () => props.pulseData,
    () => {
        if (props.pulseData?.servers) {
            servers.value = props.pulseData.servers.servers;
        }
    },
);

// Alpine.data('cpuChart', (config) => ({
//     init() {
//         let chart = new Chart(this.$refs.canvas, {
//             type: 'line',
//             data: {
//                 labels: config.labels.map(formatDate),
//                 datasets: [
//                     {
//                         label: 'CPU Percent',
//                         borderColor: '#9333ea',
//                         borderWidth: 2,
//                         borderCapStyle: 'round',
//                         data: config.data,
//                         pointHitRadius: 10,
//                         pointStyle: false,
//                         tension: 0.2,
//                         spanGaps: false,
//                     },
//                 ],
//             },
//             options: {
//                 maintainAspectRatio: false,
//                 layout: {
//                     autoPadding: false,
//                 },
//                 scales: {
//                     x: {
//                         display: false,
//                         grid: {
//                             display: false,
//                         },
//                     },
//                     y: {
//                         display: false,
//                         min: 0,
//                         max: 100,
//                         grid: {
//                             display: false,
//                         },
//                     },
//                 },
//                 plugins: {
//                     legend: {
//                         display: false,
//                     },
//                     tooltip: {
//                         mode: 'index',
//                         position: 'nearest',
//                         intersect: false,
//                         callbacks: {
//                             title: () => '',
//                             label: (context) => `${context.label} - ${context.formattedValue}%`,
//                         },
//                         displayColors: false,
//                     },
//                 },
//             },
//         });

//         Livewire.on('servers-chart-update', ({ servers }) => {
//             if (chart === undefined) {
//                 return;
//             }

//             if (servers[config.slug] === undefined && chart) {
//                 chart.destroy();
//                 chart = undefined;
//                 return;
//             }

//             chart.data.labels = Object.keys(servers[config.slug].cpu).map(formatDate);
//             chart.data.datasets[0].data = Object.values(servers[config.slug].cpu);
//             chart.update();
//         });
//     },
// }));
// Alpine.data('memoryChart', (config) => ({
//     init() {
//         let chart = new Chart(this.$refs.canvas, {
//             type: 'line',
//             data: {
//                 labels: config.labels.map(formatDate),
//                 datasets: [
//                     {
//                         label: 'Memory Used',
//                         borderColor: '#9333ea',
//                         borderWidth: 2,
//                         borderCapStyle: 'round',
//                         data: config.data,
//                         pointHitRadius: 10,
//                         pointStyle: false,
//                         tension: 0.2,
//                         spanGaps: false,
//                     },
//                 ],
//             },
//             options: {
//                 maintainAspectRatio: false,
//                 layout: {
//                     autoPadding: false,
//                 },
//                 scales: {
//                     x: {
//                         display: false,
//                         grid: {
//                             display: false,
//                         },
//                     },
//                     y: {
//                         display: false,
//                         min: 0,
//                         max: config.total,
//                         grid: {
//                             display: false,
//                         },
//                     },
//                 },
//                 plugins: {
//                     legend: {
//                         display: false,
//                     },
//                     tooltip: {
//                         mode: 'index',
//                         position: 'nearest',
//                         intersect: false,
//                         callbacks: {
//                             title: () => '',
//                             label: (context) => `${context.label} - ${context.formattedValue} MB`,
//                         },
//                         displayColors: false,
//                     },
//                 },
//             },
//         });

//         Livewire.on('servers-chart-update', ({ servers }) => {
//             if (chart === undefined) {
//                 return;
//             }

//             if (servers[config.slug] === undefined && chart) {
//                 chart.destroy();
//                 chart = undefined;
//                 return;
//             }

//             chart.data.labels = Object.keys(servers[config.slug].memory).map(formatDate);
//             chart.data.datasets[0].data = Object.values(servers[config.slug].memory);
//             chart.update();
//         });
//     },
// }));

// Alpine.data('storageChart', (config) => ({
//     init() {
//         let chart = new Chart(this.$refs.canvas, {
//             type: 'doughnut',
//             data: {
//                 labels: ['Used', 'Free'],
//                 datasets: [
//                     {
//                         data: [config.used, config.total - config.used],
//                         backgroundColor: ['#9333ea', '#c084fc30'],
//                         hoverBackgroundColor: ['#9333ea', '#c084fc30'],
//                     },
//                 ],
//             },
//             options: {
//                 borderWidth: 0,
//                 plugins: {
//                     legend: {
//                         display: false,
//                     },
//                     tooltip: {
//                         enabled: false,
//                         callbacks: {
//                             label: (context) => context.formattedValue + ' MB',
//                         },
//                         displayColors: false,
//                     },
//                 },
//             },
//         });

//         Livewire.on('servers-chart-update', ({ servers }) => {
//             const storage = servers[config.slug]?.storage?.find((storage) => storage.directory === config.directory);

//             if (chart === undefined) {
//                 return;
//             }

//             if (storage === undefined && chart) {
//                 chart.destroy();
//                 chart = undefined;
//                 return;
//             }

//             chart.data.datasets[0].data = [storage.used, storage.total - storage.used];
//             chart.update();
//         });
//     },
// }));
</script>

<template>
    <section
        :class="`overflow-x-auto overflow-y-hidden scrollbar-minimal-x  scrollbar-thumb:bg-gray-300 dark:scrollbar-thumb:bg-gray-500/50 scrollbar-track:rounded scrollbar-track:bg-gray-100 dark:scrollbar-track:bg-gray-500/10 supports-scrollbars
        max-w-full pb-2 default:col-span-full default:lg:col-span-${props.cols} default:row-span-${props.rows} ${isLoading ? 'opacity-25 animate-pulse ' : ''}${props.class ?? ''}`"
    >
        <div
            v-if="servers"
            class="max-w-full grid grid-cols-[max-content,minmax(max-content,1fr),max-content,minmax(min-content,2fr),max-content,minmax(min-content,2fr),minmax(max-content,1fr)]"
        >
            <div></div>
            <div></div>
            <div class="text-xs uppercase text-left text-gray-500 dark:text-gray-400 font-bold">CPU</div>
            <div></div>
            <div class="text-xs uppercase text-left text-gray-500 dark:text-gray-400 font-bold">Memory</div>
            <div></div>
            <div class="text-xs uppercase text-left text-gray-500 dark:text-gray-400 font-bold">Storage</div>
            <template v-for="server in Object.keys(servers)" :key="`${server}-indicator`" class="flex">
                <div :class="`flex items-center ${(Object.keys(servers).length ?? 0) > 1 ? 'py-2' : ''}`" :title="`${toTimeSpan(servers[server].updated_at)}`">
                    <div v-if="servers[server]?.recently_reported" class="w-5 flex justify-center mr-1">
                        <div class="h-1 w-1 bg-green-500 rounded-full animate-pulse"></div>
                    </div>
                    <IconSignalSlash v-else class="w-5 h-5 stroke-rose-500 mr-1" />
                </div>
                <div
                    :id="`${server}-name`"
                    :class="`flex items-center pr-8 xl:pr-12 ${(Object.keys(servers).length ?? 0) > 1 ? 'py-2' : ''} ${!servers[server].recently_reported ? 'opacity-25 animate-pulse' : ''}`"
                >
                    <IconServer class="w-6 h-6 mr-2 stroke-gray-500 dark:stroke-gray-400" />
                    <span
                        class="text-base font-bold text-gray-600 dark:text-gray-300"
                        :title="`Time: ${format_number(pulseData?.servers?.time ?? 0)}ms; Run at: ${pulseData?.servers?.runAt ? new Date(pulseData?.servers?.runAt).toLocaleDateString() : ''};`"
                    >
                        {{ servers[server]?.name }}
                    </span>
                </div>
                <div
                    :id="`${server}-cpu`"
                    :class="`flex items-center ${(Object.keys(servers).length ?? 0) > 1 ? 'py-2' : ''} ${!servers[server].recently_reported ? 'opacity-25 animate-pulse' : ''}`"
                >
                    <div class="text-lg font-bold text-gray-700 dark:text-gray-200 w-14 whitespace-nowrap tabular-nums">{{ servers[server].cpu_current }}%</div>
                </div>
                <div
                    :id="`${server}-cpu-graph`"
                    :class="`flex items-center pr-8 xl:pr-12 ${(Object.keys(servers).length ?? 0) > 1 ? 'py-2' : ''} ${!servers[server].recently_reported ? 'opacity-25 animate-pulse' : ''}`"
                >
                    <div class="w-full min-w-[5rem] h-9 relative">
                        <PulseLineChart
                            :chart-data="{
                                labels: Object.keys(servers[server].cpu),
                                datasets: [
                                    {
                                        label: 'CPU Percent',
                                        borderColor: '#9333ea',
                                        borderWidth: 2,
                                        borderCapStyle: 'round',
                                        data: Object.values(servers[server].cpu).map((value) => parseFloat(value)),
                                        pointHitRadius: 10,
                                        pointStyle: false,
                                        tension: 0.2,
                                        spanGaps: false,
                                    },
                                ],
                            }"
                            :chart-options="{
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
                            }"
                        />
                    </div>
                </div>
                <div
                    :id="`${server}-memory`"
                    :class="`flex items-center ${(Object.keys(servers).length ?? 0) > 1 ? 'py-2' : ''} ${!servers[server].recently_reported ? 'opacity-25 animate-pulse' : ''}`"
                >
                    <div class="w-36 flex-shrink-0 whitespace-nowrap tabular-nums">
                        <span class="text-lg font-bold text-gray-700 dark:text-gray-200">
                            {{ friendlyFileSize(servers[server].memory_current, 1) }}
                        </span>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400"> / {{ friendlyFileSize(servers[server].memory_total, 1) }} </span>
                    </div>
                </div>
                <div
                    :id="`${server}-memory-graph`"
                    :class="`flex items-center pr-8 xl:pr-12 ${(Object.keys(servers).length ?? 0) > 1 ? 'py-2' : ''} ${!servers[server].recently_reported ? 'opacity-25 animate-pulse' : ''}`"
                >
                    <div class="w-full min-w-[5rem] h-9 relative">
                        <PulseLineChart
                            :chart-data="{
                                labels: Object.keys(servers[server].memory),
                                datasets: [
                                    {
                                        label: 'Memory Used',
                                        borderColor: '#9333ea',
                                        borderWidth: 2,
                                        borderCapStyle: 'round',
                                        data: Object.values(servers[server].memory).map((value) => parseFloat(value)),
                                        pointHitRadius: 10,
                                        pointStyle: false,
                                        tension: 0.2,
                                        spanGaps: false,
                                    },
                                ],
                            }"
                            :chart-options="{
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
                                        max: servers[server].memory.total,
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
                                            label: (context: any) => `${context.label} - ${context.formattedValue} MB`,
                                        },
                                        displayColors: false,
                                    },
                                },
                            }"
                        />
                    </div>
                </div>
                <div
                    :id="`${server}-storage`"
                    :class="`flex items-center gap-4 ${(Object.keys(servers).length ?? 0) > 1 ? 'py-2' : ''} ${!servers[server].recently_reported ? 'opacity-25 animate-pulse' : ''}`"
                >
                    <div
                        v-for="storage in servers[server].storage"
                        :key="`${server}-storage-${storage.directory}`"
                        class="flex items-center gap-4"
                        :title="`Directory: ${storage.directory}`"
                    >
                        <div class="whitespace-nowrap tabular-nums">
                            <span class="text-lg font-bold text-gray-700 dark:text-gray-200">{{ friendlyFileSize(storage.used, 1) }}</span>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">/ {{ friendlyFileSize(storage.total, 1) }}</span>
                        </div>
                        <div>
                            <PulseDoughnutChart
                                class="h-8 w-8"
                                :chart-data="{
                                    labels: ['Used', 'Free'],
                                    datasets: [
                                        {
                                            data: [storage.used, storage.total - storage.used],
                                            backgroundColor: ['#9333ea', '#c084fc30'],
                                            hoverBackgroundColor: ['#9333ea', '#c084fc30'],
                                        },
                                    ],
                                }"
                                :chart-options="{
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
                                }"
                            />
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </section>
</template>
