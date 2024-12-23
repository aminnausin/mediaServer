<script setup lang="ts">
import type { PulseQueueResponse, PulseResponse, PulseServerResponse, PulseUsageResponse } from '@/types/types';

import { format_number, periodForHumans, pulseFormatDate } from '@/service/util';
import { ref, watch } from 'vue';

import IconQueueList from '../icons/IconQueueList.vue';
import DashboardCard from '../cards/DashboardCard.vue';
import PulseNoResults from '../pulse/PulseNoResults.vue';
import PulseScroll from '../pulse/PulseScroll.vue';
import PulseLineChart from '../charts/PulseLineChart.vue';
import IconCursorArrowRays from '../icons/IconCursorArrowRays.vue';
import IconArrowTrendingUp from '../icons/IconArrowTrendingUp.vue';
import IconClock from '../icons/IconClock.vue';
import IconScale from '../icons/IconScale.vue';
import InputSelect from '../pinesUI/InputSelect.vue';
import PulseUserCard from '../pulse/PulseUserCard.vue';

const requestOptions = [
    { value: 'requests', title: 'making requests', key: 'userRequestsConfig' },
    { value: 'slow_requests', title: 'experiencing slow endpoints', key: 'slowRequestsCounts' },
    { value: 'jobs', title: 'dispatching jobs', key: 'jobsCounts' },
];

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

const userRequestCounts = ref<PulseUsageResponse[]>();
const type = ref<{ value: 'requests' | 'slow_requests' | 'jobs'; title: string; key: string }>({
    value: 'requests',
    title: 'making requests',
    key: 'userRequestsConfig',
});

const handleSetType = (newType: { value: 'requests' | 'slow_requests' | 'jobs'; title: string; key: string }) => {
    type.value = newType;
};

const sampleRate = () => {
    switch (type.value.value) {
        case 'requests':
            return props.pulseData?.usage.userRequestsConfig.sample_rate ?? 1;
        case 'slow_requests':
            return props.pulseData?.usage.slowRequestsConfig.sample_rate ?? 1;
        default:
            return props.pulseData?.usage.jobsConfig.sample_rate ?? 1;
    }
};

watch([() => props.pulseData, type], () => {
    if (props.pulseData?.usage) {
        userRequestCounts.value =
            type.value.value === 'requests'
                ? (props.pulseData.usage.userRequestCounts ?? [])
                : type.value.value === 'slow_requests'
                  ? (props.pulseData.usage.slowRequestsCounts ?? [])
                  : (props.pulseData.usage.jobsCounts ?? []);
    }
});
</script>
<template>
    <DashboardCard
        :cols="cols"
        :rows="rows"
        :class="`${props.class} col-span-2 md:col-span-4`"
        :name="`${'Application Usage'}`"
        :title="`Time: ${format_number(pulseData?.queues?.time ?? 0)}ms; Run at: ${pulseData?.servers?.runAt ? new Date(pulseData?.servers?.runAt).toLocaleDateString() : ''};`"
        :details="`past ${periodForHumans(period)}`"
    >
        <template #icon>
            <IconArrowTrendingUp v-if="type.value === 'requests'" />
            <IconClock v-else-if="type.value === 'slow_requests'" />
            <IconScale v-else-if="type.value === 'jobs'" />
            <IconCursorArrowRays v-else />
        </template>
        <template #actions>
            <span class="flex items-center text-sm">
                <label
                    class="capitalize whitespace-nowrap h-10 flex items-center justify-between w-full py-2 p-3 text-left rounded-l-md shadow-sm text-sm ring-inset ring-1 ring-neutral-200 dark:ring-neutral-700 text-gray-900 dark:text-neutral-100 bg-primary-800 dark:bg-neutral-900"
                    >Top 10 users</label
                >
                <InputSelect
                    :placeholder="'None'"
                    :options="requestOptions"
                    class="flex-1 rounded-l-none capitalize !w-full !whitespace-nowrap"
                    title="Select usage type"
                    @selectItem="handleSetType"
                    :defaultItem="0"
                />
            </span>
        </template>

        <template #slot>
            <PulseScroll :expand="false" :loading="isLoading ?? false">
                <template #slot>
                    <PulseNoResults v-if="userRequestCounts?.length === 0" />
                    <div v-else class="grid grid-cols-1 @lg:grid-cols-2 @3xl:grid-cols-3 @6xl:grid-cols-4 gap-2 overflow-visible">
                        <PulseUserCard
                            v-for="userRequestCount in userRequestCounts"
                            :key="userRequestCount.key"
                            :user="userRequestCount.user"
                        >
                            <template #stats>
                                <span
                                    v-if="sampleRate() < 1"
                                    title="Sample rate: {{ $sampleRate }}, Raw value: {{ number_format($userRequestCount->count) }}"
                                    >~{{ format_number(userRequestCount.count * (1 / sampleRate())) }}</span
                                >
                                <template v-else>
                                    {{ format_number(userRequestCount.count) }}
                                </template>
                            </template>
                        </PulseUserCard>
                        <PulseUserCard
                            v-for="userRequestCount in userRequestCounts"
                            :key="userRequestCount.key"
                            :user="userRequestCount.user"
                        >
                            <template #stats>
                                <span
                                    v-if="sampleRate() < 1"
                                    title="Sample rate: {{ $sampleRate }}, Raw value: {{ number_format($userRequestCount->count) }}"
                                    >~{{ format_number(userRequestCount.count * (1 / sampleRate())) }}</span
                                >
                                <template v-else>
                                    {{ format_number(userRequestCount.count) }}
                                </template>
                            </template>
                        </PulseUserCard>
                        <PulseUserCard
                            v-for="userRequestCount in userRequestCounts"
                            :key="userRequestCount.key"
                            :user="userRequestCount.user"
                        >
                            <template #stats>
                                <span
                                    v-if="sampleRate() < 1"
                                    title="Sample rate: {{ $sampleRate }}, Raw value: {{ number_format($userRequestCount->count) }}"
                                    >~{{ format_number(userRequestCount.count * (1 / sampleRate())) }}</span
                                >
                                <template v-else>
                                    {{ format_number(userRequestCount.count) }}
                                </template>
                            </template>
                        </PulseUserCard>
                        <PulseUserCard
                            v-for="userRequestCount in userRequestCounts"
                            :key="userRequestCount.key"
                            :user="userRequestCount.user"
                        >
                            <template #stats>
                                <span
                                    v-if="sampleRate() < 1"
                                    title="Sample rate: {{ $sampleRate }}, Raw value: {{ number_format($userRequestCount->count) }}"
                                    >~{{ format_number(userRequestCount.count * (1 / sampleRate())) }}</span
                                >
                                <template v-else>
                                    {{ format_number(userRequestCount.count) }}
                                </template>
                            </template>
                        </PulseUserCard>
                        <PulseUserCard
                            v-for="userRequestCount in userRequestCounts"
                            :key="userRequestCount.key"
                            :user="userRequestCount.user"
                        >
                            <template #stats>
                                <span
                                    v-if="sampleRate() < 1"
                                    title="Sample rate: {{ $sampleRate }}, Raw value: {{ number_format($userRequestCount->count) }}"
                                    >~{{ format_number(userRequestCount.count * (1 / sampleRate())) }}</span
                                >
                                <template v-else>
                                    {{ format_number(userRequestCount.count) }}
                                </template>
                            </template>
                        </PulseUserCard>
                    </div>
                </template>
            </PulseScroll>
        </template>
    </DashboardCard>
</template>
