<script setup lang="ts">
import type { PulseResponse, PulseRquestsResponse } from '@/types/types';

import { format_number, periodForHumans, pulseFormatDate } from '@/service/util';
import { ref, watch } from 'vue';

import IconArrowsLeftRight from '@/components/icons/IconArrowsLeftRight.vue';
import PulseLineChart from '@/components/charts/PulseLineChart.vue';
import PulseNoResults from '@/components/pulse/PulseNoResults.vue';
import DashboardCard from '@/components/cards/DashboardCard.vue';
import PulseScroll from '@/components/pulse/PulseScroll.vue';

const validPeriods = ['1_hour', '6_hours', '24_hours', '7_days'];

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

const requests = ref<{ [key: string]: PulseRquestsResponse }>();

function scale(data: { [key: string]: any }) {
    return Object.values(data).map((value) => value * (1 / (props.pulseData?.requests?.config.sample_rate ?? 1)));
}

function getMaxReading(readings: { [key: string]: { [key: string]: string | null } }): number {
    // Flatten the nested object structure and convert values to numbers
    const flattenedValues = Object.values(readings).flatMap((request) => Object.values(request));
    const numberValues = flattenedValues.map((value) => (value !== null && !isNaN(Number(value)) ? Number(value) : -Infinity));
    return Math.max(...numberValues);
}

watch(
    () => props.pulseData,
    () => {
        if (props.pulseData?.requests) {
            requests.value = props.pulseData.requests.requests;
        }
    },
);
</script>
<template>
    <DashboardCard
        :rows="rows"
        :class="props.class"
        name="Requests"
        :title="`Time: ${format_number(pulseData?.requests?.time ?? 0)}ms; Run at: ${pulseData?.requests?.runAt ? new Date(pulseData?.requests?.runAt).toLocaleDateString() : ''};`"
        :details="`past ${validPeriods.indexOf(period) !== -1 ? periodForHumans(period) : periodForHumans(validPeriods[0])}`"
    >
        <template #icon>
            <IconArrowsLeftRight />
        </template>
        <template #actions>
            <div class="flex flex-wrap gap-4">
                <div
                    v-if="pulseData?.requests?.config.record_informational"
                    class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400 font-medium"
                >
                    <div class="h-0.5 w-3 rounded-full" style="background-color: rgba(29, 153, 172, 0.5)"></div>
                    Informational
                </div>
                <div
                    v-if="pulseData?.requests?.config.record_successful"
                    class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400 font-medium"
                >
                    <div class="h-0.5 w-3 rounded-full bg-[#9333ea]"></div>
                    Successful
                </div>
                <div
                    v-if="pulseData?.requests?.config.record_redirection"
                    class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400 font-medium"
                >
                    <div class="h-0.5 w-3 rounded-full bg-[rgba(107,114,128,0.5)]"></div>
                    Redirection
                </div>
                <div
                    v-if="pulseData?.requests?.config.record_client_error"
                    class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400 font-medium"
                >
                    <div class="h-0.5 w-3 rounded-full bg-[#eab308]"></div>
                    Client Error
                </div>
                <div
                    v-if="pulseData?.requests?.config.record_server_error"
                    class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400 font-medium"
                >
                    <div class="h-0.5 w-3 rounded-full bg-[#e11d48]"></div>
                    Server Error
                </div>
            </div>
        </template>

        <template #slot>
            <PulseScroll :expand="true" :loading="isLoading ?? true">
                <template #slot>
                    <PulseNoResults v-if="!requests || (Object.keys(requests)?.length ?? 0) == 0" />
                    <div v-else-if="requests" class="grid gap-3 mx-px mb-px">
                        <div v-for="request in Object.keys(requests)" :key="request">
                            <div class="mt-3 relative">
                                <div
                                    class="absolute -left-px -top-2 max-w-fit h-4 flex items-center px-1 text-xs leading-none text-white font-bold bg-purple-500 rounded after:[--triangle-size:4px] after:border-l-purple-500 after:absolute after:right-[calc(-1*var(--triangle-size))] after:top-[calc(50%-var(--triangle-size))] after:border-t-[length:var(--triangle-size)] after:border-b-[length:var(--triangle-size)] after:border-l-[length:var(--triangle-size)] after:border-transparent"
                                >
                                    <span
                                        v-if="pulseData?.requests?.config.sample_rate && pulseData.requests.config.sample_rate < 1"
                                        :title="`Sample rate: ${pulseData.requests.config.sample_rate}, Raw value: ${format_number(getMaxReading(requests[request]))}`"
                                        >~{{
                                            format_number(getMaxReading(requests[request]) * (1 / pulseData.requests.config.sample_rate))
                                        }}</span
                                    >
                                    <template v-else>
                                        {{ format_number(getMaxReading(requests[request])) }}
                                    </template>
                                </div>

                                <div class="h-14">
                                    <PulseLineChart
                                        :class="' dark:!bg-primary-dark-800 !bg-primary-900 '"
                                        :chart-data="{
                                            labels: Object.keys(requests[request][Object.keys(requests[request])[0]]).map((v) =>
                                                pulseFormatDate(v),
                                            ),
                                            datasets: [
                                                {
                                                    label: 'Server Error',
                                                    borderColor: '#e11d48',
                                                    data: scale(requests[request].server_error),
                                                },
                                                {
                                                    label: 'Client Error',
                                                    borderColor: '#eab308',
                                                    data: scale(requests[request].client_error),
                                                },
                                                {
                                                    label: 'Successful',
                                                    borderColor: '#9333ea',
                                                    data: scale(requests[request].successful),
                                                },
                                                {
                                                    label: 'Informational',
                                                    borderColor: 'rgba(29,153,172,0.5)',
                                                    data: scale(requests[request].informational),
                                                },
                                                {
                                                    label: 'Redirection',
                                                    borderColor: 'rgba(107,114,128,0.5)',
                                                    data: scale(requests[request].redirection),
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
                                                    max: getMaxReading(requests[request]),
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
