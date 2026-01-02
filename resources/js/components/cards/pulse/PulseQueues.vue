<script setup lang="ts">
import type { PulseResponse } from '@/types/pulseTypes';

import { format_number, periodForHumans, pulseFormatDate, getMaxReading } from '@/service/pulseUtil';
import { computed } from 'vue';

import PulseLineChart from '@/components/charts/PulseLineChart.vue';
import PulseNoResults from '@/components/pulse/PulseNoResults.vue';
import DashboardCard from '@/components/cards/layout/DashboardCard.vue';
import IconQueueList from '@/components/icons/IconQueueList.vue';
import PulseScroll from '@/components/pulse/PulseScroll.vue';

const validPeriods = ['1_hour', '6_hours', '24_hours', '7_days'];

const props = withDefaults(
    defineProps<{
        cols?: number | string;
        rows?: number;
        pulseData?: PulseResponse;
        isLoading?: boolean;
        period: string;
    }>(),
    {
        cols: 2,
        rows: 4,
    },
);

const queues = computed(() => {
    return props.pulseData?.queues?.queues ?? {};
});
function scale(data: { [key: string]: any }) {
    return Object.values(data).map((value) => value * (1 / (props.pulseData?.queues?.config.sample_rate ?? 1)));
}
</script>
<template>
    <DashboardCard
        :rows="rows"
        name="Queues"
        :title="`Time: ${format_number(pulseData?.queues?.time ?? 0)}ms; Run at: ${pulseData?.queues?.runAt ? new Date(pulseData?.queues?.runAt).toLocaleDateString() : ''};`"
        :details="`past ${validPeriods.includes(period) ? periodForHumans(period) : periodForHumans(validPeriods[0])}`"
    >
        <template #icon>
            <IconQueueList />
        </template>
        <template #actions>
            <div class="flex flex-wrap gap-4 text-xs font-medium text-gray-600 dark:text-gray-400">
                <div class="flex items-center gap-2">
                    <div class="h-0.5 w-3 rounded-full bg-[rgba(107,114,128,0.5)]"></div>
                    Queued
                </div>
                <div class="flex items-center gap-2">
                    <div class="h-0.5 w-3 rounded-full bg-[rgba(147,51,234,0.5)]"></div>
                    Processing
                </div>
                <div class="flex items-center gap-2 font-medium">
                    <div class="h-0.5 w-3 rounded-full bg-[#9333ea]"></div>
                    Processed
                </div>
                <div class="flex items-center gap-2">
                    <div class="h-0.5 w-3 rounded-full bg-[#eab308]"></div>
                    Released
                </div>
                <div class="flex items-center gap-2">
                    <div class="h-0.5 w-3 rounded-full bg-[#e11d48]"></div>
                    Failed
                </div>
            </div>
        </template>

        <PulseScroll :expand="true" :loading="isLoading ?? true">
            <PulseNoResults :isLoading="isLoading" v-if="!queues || (Object.keys(queues)?.length ?? 0) == 0" />
            <div v-else-if="queues" class="mx-px mb-px grid gap-3">
                <div v-for="queue in Object.keys(queues)" :key="queue">
                    <h3 class="text-foreground-6 font-bold">
                        <template v-if="pulseData?.queues?.showConnection">
                            {{ queue }}
                        </template>
                        <template v-else>
                            {{ queue.substring(queue.indexOf(':') + 1) }}
                        </template>
                    </h3>
                    <div class="relative mt-3">
                        <div
                            class="bg-primary-muted after:border-l-primary-muted absolute -top-2 -left-px flex h-4 max-w-fit items-center rounded-sm px-1 text-xs leading-none font-bold text-white after:absolute after:top-[calc(50%-var(--triangle-size))] after:right-[calc(-1*var(--triangle-size))] after:border-t-(length:--triangle-size) after:border-b-(length:--triangle-size) after:border-l-(length:--triangle-size) after:border-transparent after:[--triangle-size:4px]"
                        >
                            <span
                                v-if="pulseData?.queues?.config.sample_rate && pulseData.queues.config.sample_rate < 1"
                                :title="`Sample rate: ${pulseData.queues.config.sample_rate}, Raw value: ${format_number(getMaxReading(queues[queue]))}`"
                                >~{{ format_number(getMaxReading(queues[queue]) * (1 / pulseData.queues.config.sample_rate)) }}</span
                            >
                            <template v-else>
                                {{ format_number(getMaxReading(queues[queue])) }}
                            </template>
                        </div>

                        <div class="h-14">
                            <PulseLineChart
                                :class="'dark:bg-primary-dark-800! bg-primary-900!'"
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
                                                borderColor: (ctx: any) => (ctx.p0.raw === 0 && ctx.p1.raw === 0 ? 'transparent' : undefined),
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
        </PulseScroll>
    </DashboardCard>
</template>
