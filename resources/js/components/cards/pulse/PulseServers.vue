<script setup lang="ts">
import type { PulseResponse, PulseServerResponse } from '@/types/pulseTypes';

import { format_number, friendlyFileSize } from '@/service/pulseUtil';
import { toTimeSpan } from '@/service/util';
import { computed } from 'vue';

import PulseServersPlaceholder from '@/components/pulse/PulseServersPlaceholder.vue';
import PulseDoughnutChart from '@/components/charts/PulseDoughnutChart.vue';
import IconSignalSlash from '@/components/icons/IconSignalSlash.vue';
import PulseLineChart from '@/components/charts/PulseLineChart.vue';
import IconServer from '@/components/icons/IconServer.vue';

const props = withDefaults(
    defineProps<{
        cols?: number | string;
        rows?: number;
        pulseData?: PulseResponse;
        isLoading?: boolean;
    }>(),
    {
        cols: 'full',
        rows: 1,
    },
);

const servers = computed<{ [key: string]: PulseServerResponse } | undefined>(() => props.pulseData?.servers.servers);
const serverKeys = computed(() => Object.keys(servers.value ?? {}));
</script>

<template>
    <section
        :class="[
            'scrollbar-minimal scrollbar-track:rounded supports-scrollbars max-w-full overflow-x-auto overflow-y-hidden',
            `default:col-span-full default:lg:col-span-${props.cols} default:row-span-${props.rows}`,
            { 'pb-2': serverKeys.length > 0 || isLoading, hidden: serverKeys.length === 0 && !isLoading },
        ]"
    >
        <div
            v-if="servers && !isLoading"
            class="grid max-w-full grid-cols-[max-content_minmax(max-content,1fr)_max-content_minmax(min-content,2fr)_max-content_minmax(min-content,2fr)_minmax(max-content,1fr)]"
        >
            <div></div>
            <div></div>
            <div class="text-foreground-2 text-left text-xs font-bold uppercase">CPU</div>
            <div></div>
            <div class="text-foreground-2 text-left text-xs font-bold uppercase">Memory</div>
            <div></div>
            <div class="text-foreground-2 text-left text-xs font-bold uppercase">Storage</div>
            <template v-for="[server, data] in Object.entries(servers)" :key="`${server}-indicator`" class="flex">
                <div :class="['flex items-center', { 'py-2': serverKeys.length > 1 }]" :title="`${toTimeSpan(data.updated_at)}`">
                    <div v-if="data?.recently_reported" class="mr-1 flex w-5 justify-center">
                        <div class="h-1 w-1 animate-pulse rounded-full bg-green-500"></div>
                    </div>
                    <IconSignalSlash v-else class="mr-1 h-5 w-5 stroke-rose-500" />
                </div>
                <div :id="`${server}-name`" :class="['flex items-center pr-8 xl:pr-12', { 'py-2': serverKeys.length > 1, 'animate-pulse opacity-25': !data.recently_reported }]">
                    <IconServer class="stroke-foreground-2 mr-2 h-6 w-6" />
                    <span
                        class="text-foreground-1 dark:text-foreground-4 text-base font-bold"
                        :title="`Time: ${format_number(pulseData?.servers?.time ?? 0)}ms; Run at: ${pulseData?.servers?.runAt ? new Date(pulseData?.servers?.runAt).toLocaleDateString() : ''};`"
                    >
                        {{ data?.name }}
                    </span>
                </div>
                <div :id="`${server}-cpu`" :class="['flex items-center', { 'py-2': serverKeys.length > 1, 'animate-pulse opacity-25': !data.recently_reported }]">
                    <div class="w-14 text-lg font-bold whitespace-nowrap text-neutral-700 tabular-nums dark:text-neutral-200">{{ data.cpu_current }}%</div>
                </div>
                <div
                    :id="`${server}-cpu-graph`"
                    :class="['flex items-center pr-8 xl:pr-12', { 'py-2': serverKeys.length > 1, 'animate-pulse opacity-25': !data.recently_reported }]"
                >
                    <div class="h-9 w-full min-w-20">
                        <PulseLineChart
                            :chart-data="{
                                labels: Object.keys(data.cpu),
                                datasets: [
                                    {
                                        label: 'CPU Percent',
                                        borderColor: '#9333ea',
                                        borderWidth: 2,
                                        borderCapStyle: 'round',
                                        data: Object.values(data.cpu).map((value) => parseFloat(value)),
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
                <div :id="`${server}-memory`" :class="['flex items-center', { 'py-2': serverKeys.length > 1, 'animate-pulse opacity-25': !data.recently_reported }]">
                    <div class="w-36 shrink-0 whitespace-nowrap tabular-nums">
                        <span class="text-lg font-bold text-neutral-700 dark:text-neutral-200">
                            {{ friendlyFileSize(data.memory_current, 1) }}
                        </span>
                        <span class="text-sm font-medium text-neutral-500 dark:text-neutral-400"> / {{ friendlyFileSize(data.memory_total, 1) }} </span>
                    </div>
                </div>
                <div
                    :id="`${server}-memory-graph`"
                    :class="['flex items-center pr-8 xl:pr-12', { 'py-2': serverKeys.length > 1, 'animate-pulse opacity-25': !data.recently_reported }]"
                >
                    <div class="h-9 w-full min-w-20">
                        <PulseLineChart
                            :chart-data="{
                                labels: Object.keys(data.memory),
                                datasets: [
                                    {
                                        label: 'Memory Used',
                                        borderColor: '#9333ea',
                                        borderWidth: 2,
                                        borderCapStyle: 'round',
                                        data: Object.values(data.memory).map((value) => parseFloat(value)),
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
                                        max: data.memory.total,
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
                <div :id="`${server}-storage`" :class="['flex items-center gap-4', { 'py-2': serverKeys.length > 1, 'animate-pulse opacity-25': !data.recently_reported }]">
                    <div v-for="storage in data.storage" :key="`${server}-storage-${storage.directory}`" class="flex items-center gap-4" :title="`Directory: ${storage.directory}`">
                        <div class="whitespace-nowrap tabular-nums">
                            <span class="text-lg font-bold text-neutral-700 dark:text-neutral-200">{{ friendlyFileSize(storage.used, 1) }}</span>
                            <span class="text-sm font-medium text-neutral-500 dark:text-neutral-400">/ {{ friendlyFileSize(storage.total, 1) }}</span>
                        </div>
                        <PulseDoughnutChart
                            class="size-9"
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
            </template>
        </div>
        <template v-else>
            <PulseServersPlaceholder cols="6" :rows="1" />
        </template>
    </section>
</template>
