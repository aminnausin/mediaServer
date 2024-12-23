<script setup lang="ts">
import type { PulseQueueResponse, PulseResponse, PulseServerResponse } from '@/types/types';

import { periodForHumans, pulseFormatDate } from '@/service/util';
import { ref, watch } from 'vue';

import IconQueueList from '../icons/IconQueueList.vue';
import DashboardCard from '../cards/DashboardCard.vue';
import PulseNoResults from '../pulse/PulseNoResults.vue';
import PulseScroll from '../pulse/PulseScroll.vue';
import PulseLineChart from '../charts/PulseLineChart.vue';

const props = withDefaults(
    defineProps<{
        cols?: number | string;
        rows?: number;
        class?: string;
        pulseData?: PulseResponse;
        isLoading?: boolean;
        period: string;
    }>(),
    {
        cols: 2,
        rows: 4,
    },
);

const queues = ref<{ [key: string]: PulseQueueResponse }>();

function scale(data: { [key: string]: any }) {
    return Object.values(data).map((value) => value * (1 / (props.pulseData?.queues?.config.sample_rate ?? 1)));
}

function getMaxReading(readings: { [key: string]: { [key: string]: string | null } }): number {
    // Flatten the nested object structure and convert values to numbers
    const flattenedValues = Object.values(readings).flatMap((queue) => Object.values(queue));
    const numberValues = flattenedValues.map((value) => (value !== null && !isNaN(Number(value)) ? Number(value) : -Infinity));
    return Math.max(...numberValues);
}

watch(
    () => props.pulseData,
    () => {
        if (props.pulseData?.queues) {
            queues.value = props.pulseData.queues.queues;
        }
    },
);
// Alpine.data(
//     'queueChart',
//     (config: {
//         readings: { queued: any; processing: any; released: any; processed: any; failed: any };
//         sampleRate: number;
//         queue: string | number;
//     }) => ({
//         init() {
//             let chart = new Chart(this.$refs.canvas, {
//                 type: 'line',
//                 data: {
//                     labels: this.labels(config.readings),
//                     datasets: [
//                         {
//                             label: 'Queued',
//                             borderColor: 'rgba(107,114,128,0.5)',
//                             data: this.scale(config.readings.queued),
//                             order: 4,
//                         },
//                         {
//                             label: 'Processing',
//                             borderColor: 'rgba(147,51,234,0.5)',
//                             data: this.scale(config.readings.processing),
//                             order: 3,
//                         },
//                         {
//                             label: 'Released',
//                             borderColor: '#eab308',
//                             data: this.scale(config.readings.released),
//                             order: 2,
//                         },
//                         {
//                             label: 'Processed',
//                             borderColor: '#9333ea',
//                             data: this.scale(config.readings.processed),
//                             order: 1,
//                         },
//                         {
//                             label: 'Failed',
//                             borderColor: '#e11d48',
//                             data: this.scale(config.readings.failed),
//                             order: 0,
//                         },
//                     ],
//                 },
//                 options: {
//                     maintainAspectRatio: false,
//                     layout: {
//                         autoPadding: false,
//                         padding: {
//                             top: 1,
//                         },
//                     },
//                     datasets: {
//                         line: {
//                             borderWidth: 2,
//                             borderCapStyle: 'round',
//                             pointHitRadius: 10,
//                             pointStyle: false,
//                             tension: 0.2,
//                             spanGaps: false,
//                             segment: {
//                                 borderColor: (ctx: { p0: { raw: number }; p1: { raw: number } }) =>
//                                     ctx.p0.raw === 0 && ctx.p1.raw === 0 ? 'transparent' : undefined,
//                             },
//                         },
//                     },
//                     scales: {
//                         x: {
//                             display: false,
//                         },
//                         y: {
//                             display: false,
//                             min: 0,
//                             max: this.highest(config.readings),
//                         },
//                     },
//                     plugins: {
//                         legend: {
//                             display: false,
//                         },
//                         tooltip: {
//                             mode: 'index',
//                             position: 'nearest',
//                             intersect: false,
//                             callbacks: {
//                                 beforeBody: (context: any[]) =>
//                                     context
//                                         .map(
//                                             (item: { dataset: { label: any }; formattedValue: any }) =>
//                                                 `${item.dataset.label}: ${config.sampleRate < 1 ? '~' : ''}${item.formattedValue}`,
//                                         )
//                                         .join(', '),
//                                 label: () => null,
//                             },
//                         },
//                     },
//                 },
//             });

//             Livewire.on('queues-chart-update', ({ queues }) => {
//                 if (chart === undefined) {
//                     return;
//                 }

//                 if (queues[config.queue] === undefined && chart) {
//                     chart.destroy();
//                     chart = undefined;
//                     return;
//                 }

//                 chart.data.labels = this.labels(queues[config.queue]);
//                 chart.options.scales.y.max = this.highest(queues[config.queue]);
//                 chart.data.datasets[0].data = this.scale(queues[config.queue].queued);
//                 chart.data.datasets[1].data = this.scale(queues[config.queue].processing);
//                 chart.data.datasets[2].data = this.scale(queues[config.queue].released);
//                 chart.data.datasets[3].data = this.scale(queues[config.queue].processed);
//                 chart.data.datasets[4].data = this.scale(queues[config.queue].failed);
//                 chart.update();
//             });
//         },
//         labels(readings: { queued: {} }) {
//             return Object.keys(readings.queued).map(formatDate);
//         },
//         scale(data: { [s: string]: unknown } | ArrayLike<unknown>) {
//             return Object.values(data).map((value) => value * (1 / config.sampleRate));
//         },
//         highest(readings: { [s: string]: unknown } | ArrayLike<unknown>) {
//             return Math.max(...Object.values(readings).map((dataset) => Math.max(...Object.values(dataset)))) * (1 / config.sampleRate);
//         },
//     }),
// );
</script>
<template>
    <DashboardCard
        :rows="rows"
        :class="props.class"
        name="Queues"
        :title="`Time: ${Intl.NumberFormat().format(pulseData?.queues?.time ?? 0)}ms; Run at: ${pulseData?.servers?.runAt ? new Date(pulseData?.servers?.runAt).toLocaleDateString() : ''};`"
        :details="`past ${periodForHumans(period)}`"
    >
        <template #icon>
            <IconQueueList />
        </template>
        <template #actions>
            <div class="flex flex-wrap gap-4">
                <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400 font-medium">
                    <div class="h-0.5 w-3 rounded-full bg-[rgba(107,114,128,0.5)]"></div>
                    Queued
                </div>
                <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400 font-medium">
                    <div class="h-0.5 w-3 rounded-full bg-[rgba(147,51,234,0.5)]"></div>
                    Processing
                </div>
                <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400 font-medium">
                    <div class="h-0.5 w-3 rounded-full bg-[#9333ea]"></div>
                    Processed
                </div>
                <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400 font-medium">
                    <div class="h-0.5 w-3 rounded-full bg-[#eab308]"></div>
                    Released
                </div>
                <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400 font-medium">
                    <div class="h-0.5 w-3 rounded-full bg-[#e11d48]"></div>
                    Failed
                </div>
            </div>
        </template>

        <template #slot>
            <PulseScroll :expand="true" :loading="isLoading ?? true">
                <template #slot>
                    <PulseNoResults v-if="!queues || (Object.keys(queues)?.length ?? 0) == 0" />
                    <div v-else-if="queues" class="grid gap-3 mx-px mb-px">
                        <div v-for="queue in Object.keys(queues)" :key="queue">
                            <h3 class="font-bold text-gray-700 dark:text-gray-300">
                                <template v-if="pulseData?.queues?.showConnection">
                                    {{ queue }}
                                </template>
                                <template v-else>
                                    {{ queue.substring(queue.indexOf(':') + 1) }}
                                </template>
                            </h3>
                            <div class="mt-3 relative">
                                <div
                                    class="absolute -left-px -top-2 max-w-fit h-4 flex items-center px-1 text-xs leading-none text-white font-bold bg-purple-500 rounded after:[--triangle-size:4px] after:border-l-purple-500 after:absolute after:right-[calc(-1*var(--triangle-size))] after:top-[calc(50%-var(--triangle-size))] after:border-t-[length:var(--triangle-size)] after:border-b-[length:var(--triangle-size)] after:border-l-[length:var(--triangle-size)] after:border-transparent"
                                >
                                    <span
                                        v-if="pulseData?.queues?.config.sample_rate && pulseData.queues.config.sample_rate < 1"
                                        :title="`Sample rate: ${pulseData.queues.config.sample_rate}, Raw value: ${Intl.NumberFormat().format(getMaxReading(queues[queue]))}`"
                                        >~{{
                                            Intl.NumberFormat().format(
                                                getMaxReading(queues[queue]) * (1 / pulseData.queues.config.sample_rate),
                                            )
                                        }}</span
                                    >
                                    <template v-else>
                                        {{ Intl.NumberFormat().format(getMaxReading(queues[queue])) }}
                                    </template>
                                </div>

                                <div class="h-14">
                                    <PulseLineChart
                                        :chart-data="{
                                            labels: Object.keys(queues[queue].queued).map((v) => pulseFormatDate(v)),
                                            datasets: [
                                                {
                                                    label: 'Queued',
                                                    borderColor: 'rgba(107,114,128,0.5)',
                                                    data: scale(queues[queue].queued),
                                                    order: 4,
                                                },
                                                {
                                                    label: 'Processing',
                                                    borderColor: 'rgba(147,51,234,0.5)',
                                                    data: scale(queues[queue].processing),
                                                    order: 3,
                                                },
                                                {
                                                    label: 'Released',
                                                    borderColor: '#eab308',
                                                    data: scale(queues[queue].released),
                                                    order: 2,
                                                },
                                                {
                                                    label: 'Processed',
                                                    borderColor: '#9333ea',
                                                    data: scale(queues[queue].processed),
                                                    order: 1,
                                                },
                                                {
                                                    label: 'Failed',
                                                    borderColor: '#e11d48',
                                                    data: scale(queues[queue].failed),
                                                    order: 0,
                                                },
                                            ],
                                        }"
                                        :chart-options="{
                                            maintainAspectRatio: false,
                                            layout: {
                                                autoPadding: false,
                                                padding: {
                                                    top: 1,
                                                },
                                            },
                                            datasets: {
                                                line: {
                                                    borderWidth: 2,
                                                    borderCapStyle: 'round',
                                                    pointHitRadius: 10,
                                                    pointStyle: false,
                                                    tension: 0.2,
                                                    spanGaps: false,
                                                    segment: {
                                                        borderColor: (ctx: any) =>
                                                            ctx.p0.raw === 0 && ctx.p1.raw === 0 ? 'transparent' : undefined,
                                                    },
                                                },
                                            },
                                            scales: {
                                                x: {
                                                    display: false,
                                                },
                                                y: {
                                                    display: false,
                                                    min: 0,
                                                    max: getMaxReading(queues[queue]),
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
                                                        beforeBody: (context: any[]) => {
                                                            return context
                                                                .map((item) => {
                                                                    return `${item.dataset.label}: ${(pulseData?.queues?.config.sample_rate ?? 2) < 1 ? '~' : ''}${item.formattedValue}`;
                                                                })
                                                                .join(', ');
                                                        },
                                                        label: () => null,
                                                    },
                                                },
                                            },
                                        }"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </PulseScroll>
        </template>
    </DashboardCard>
</template>
