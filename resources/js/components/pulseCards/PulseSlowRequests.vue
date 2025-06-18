<script setup lang="ts">
import type { PulseResponse } from '@/types/pulseTypes';

import { format_number, periodForHumans } from '@/service/pulseUtil';
import { computed, ref } from 'vue';
import { sortObject } from '@/service/sort/baseSort';

import PulseHttpMethodBadge from '@/components/pulse/PulseHttpMethod-Badge.vue';
import IconArrowsLeftRight from '@/components/icons/IconArrowsLeftRight.vue';
import PulseSelectLabel from '@/components/pulse/PulseSelectLabel.vue';
import PulseNoResults from '@/components/pulse/PulseNoResults.vue';
import DashboardCard from '@/components/cards/DashboardCard.vue';
import PulseScroll from '@/components/pulse/PulseScroll.vue';
import InputSelect from '@/components/pinesUI/InputSelect.vue';
import PulseTable from '@/components/pulse/PulseTable.vue';
import PulseThead from '@/components/pulse/PulseThead.vue';
import PulseTd from '@/components/pulse/PulseTd.vue';
import PulseTh from '@/components/pulse/PulseTh.vue';

const validPeriods = ['1_hour', '6_hours', '24_hours', '7_days'];

const exceptionOptions = [
    { value: 'slowest', title: 'slowest' },
    { value: 'count', title: 'count' },
];

const props = withDefaults(
    defineProps<{
        cols?: number | string;
        rows?: number;
        class?: string;
        pulseData: PulseResponse;
        isLoading?: boolean;
        period: string;
    }>(),
    {
        cols: 'full',
        rows: 4,
    },
);

const sort = ref<{ value: 'count' | 'slowest'; title: string }>({
    value: 'slowest',
    title: 'slowest',
});

const handleSetSort = (newSortKey: { value: 'count' | 'slowest'; title: string }) => {
    sort.value = newSortKey;
};

const requests = computed(() => {
    const list = props.pulseData.slow_requests?.slowRequests ?? [];
    const sortKey = sort.value.value as keyof (typeof list)[0];

    return [...list].sort(sortObject(sortKey, -1, []));
});

const config = computed(() => {
    return props.pulseData.slow_requests.config;
});
</script>
<template>
    <DashboardCard
        :rows="rows"
        :cols="cols"
        :class="props.class"
        name="Slow Requests"
        :title="`Time: ${format_number(pulseData.slow_requests?.time ?? 0)}ms; Run at: ${pulseData.slow_requests?.runAt ? new Date(pulseData.slow_requests?.runAt).toLocaleDateString() : ''};`"
        :details="`${pulseData.slow_requests.config?.threshold ?? 1000}ms threshold, past ${validPeriods.indexOf(period) !== -1 ? periodForHumans(period) : periodForHumans(validPeriods[0])}`"
    >
        <template #icon>
            <IconArrowsLeftRight />
        </template>

        <template #actions>
            <PulseSelectLabel controlId="Select-Slow-Requests-Order"> Sort By </PulseSelectLabel>

            <InputSelect
                :placeholder="'None'"
                :options="exceptionOptions"
                class="flex-1 rounded-l-none capitalize !w-full !whitespace-nowrap"
                title="Select usage type"
                @selectItem="handleSetSort"
                :defaultItem="0"
                id="Select-Slow-Requests-Order"
            />
        </template>

        <PulseScroll :expand="false" :loading="isLoading ?? false">
            <div class="min-h-full flex flex-col">
                <PulseNoResults :isLoading="isLoading" v-if="requests.length === 0" />
                <PulseTable v-else>
                    <colgroup>
                        <col class="w-0" />
                        <col class="w-full" />
                        <col class="w-0" />
                        <col class="w-0" />
                    </colgroup>
                    <PulseThead>
                        <tr>
                            <PulseTh class="text-left">Method</PulseTh>
                            <PulseTh class="text-left">Route</PulseTh>
                            <PulseTh class="text-right">Count</PulseTh>
                            <PulseTh class="text-right">Slowest</PulseTh>
                        </tr>
                    </PulseThead>
                    <tbody class="">
                        <template v-for="(slowRequest, index) in requests.slice(0, 100)" :key="index">
                            <tr class="h-2 first:h-0"></tr>
                            <tr>
                                <PulseTd>
                                    <PulseHttpMethodBadge :method="slowRequest.method" />
                                </PulseTd>
                                <PulseTd class="overflow-hidden max-w-[1px]">
                                    <code class="block text-xs text-neutral-900 dark:text-neutral-100 truncate" :title="slowRequest.uri">
                                        {{ slowRequest.uri }}
                                    </code>
                                    <p v-if="slowRequest.action" class="mt-1 text-xs text-neutral-500 dark:text-neutral-400 truncate" :title="slowRequest.action">
                                        {{ slowRequest.action }}
                                    </p>
                                    <p v-if="Array.isArray(config?.threshold)" class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                        {{ slowRequest.threshold }}ms threshold
                                    </p>
                                </PulseTd>
                                <PulseTd :numeric="true" class="text-neutral-700 dark:text-neutral-300 font-bold">
                                    <span v-if="config.sample_rate < 1" :title="`Sample rate: ${config.sample_rate}, Raw value: ${format_number(slowRequest.count)}`">
                                        ~{{ format_number(slowRequest.count * (1 / config.sample_rate)) }}
                                    </span>
                                    <template v-else>
                                        {{ format_number(slowRequest.count) }}
                                    </template>
                                </PulseTd>
                                <PulseTd :numeric="true" class="text-neutral-700 dark:text-neutral-300">
                                    <strong v-if="!slowRequest.slowest">Unknown</strong>
                                    <template v-else>
                                        <strong>{{ format_number(slowRequest.slowest) ?? '<1' }}</strong> ms
                                    </template>
                                </PulseTd>
                            </tr>
                        </template>
                    </tbody>
                </PulseTable>
                <div v-if="requests.length > 100" class="mt-2 text-xs text-neutral-400 text-center">Limited to 100 entries</div>
            </div>
        </PulseScroll>
    </DashboardCard>
</template>
