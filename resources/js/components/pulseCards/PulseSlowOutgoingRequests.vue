<script setup lang="ts">
import type { PulseResponse } from '@/types/pulseTypes';

import { format_number, periodForHumans } from '@/service/pulseUtil';
import { computed, ref } from 'vue';
import { sortObject } from '@/service/sort/baseSort';

import IconInformationCircle from '@/components/icons/IconInformationCircle.vue';
import PulseHttpMethodBadge from '@/components/pulse/PulseHttpMethod-Badge.vue';
import IconCloudArrowUp from '@/components/icons/IconCloudArrowUp.vue';
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

const slowOutgoingRequests = computed(() => {
    const list = props.pulseData.slow_outgoing_requests?.slowOutgoingRequests ?? [];
    const sortKey = sort.value.value as keyof (typeof list)[0];

    return [...list].sort(sortObject(sortKey, -1, []));
});

const config = computed(() => {
    return props.pulseData.slow_outgoing_requests.config;
});

function getDomainFromUrl(input: string): string | null {
    try {
        return new URL(input).hostname;
    } catch {
        return null;
    }
}
</script>
<template>
    <DashboardCard
        :rows="rows"
        :cols="cols"
        name="Slow Outgoing Requests"
        :title="`Time: ${format_number(pulseData.slow_outgoing_requests?.time ?? 0)}ms; Run at: ${pulseData.slow_outgoing_requests?.runAt ? new Date(pulseData.slow_outgoing_requests?.runAt).toLocaleDateString() : ''};`"
        :details="`${config?.threshold ?? 1000}ms threshold, past ${validPeriods.indexOf(period) !== -1 ? periodForHumans(period) : periodForHumans(validPeriods[0])}`"
    >
        <template #icon>
            <IconCloudArrowUp />
        </template>

        <template #actions>
            <button
                :title="`URIs may be normalized using groups.\n\nThere ${config.groups.length === 1 ? 'is' : 'are'} currently ${config.groups.length} ${config.groups.length === 1 ? 'group' : 'groups'} configured.`"
            >
                <IconInformationCircle class="w-5 h-5 stroke-gray-400 dark:stroke-gray-600" />
            </button>
            <PulseSelectLabel controlId="Select-Slow-Outgoing-Requests-Order"> Sort By </PulseSelectLabel>
            <InputSelect
                :placeholder="'None'"
                :options="exceptionOptions"
                class="flex-1 rounded-l-none capitalize w-full! whitespace-nowrap!"
                title="Select usage type"
                @selectItem="handleSetSort"
                :defaultItem="0"
                id="Select-Slow-Outgoing-Requests-Order"
            />
        </template>

        <PulseScroll :expand="false" :loading="isLoading ?? false">
            <div class="min-h-full flex flex-col">
                <PulseNoResults :isLoading="isLoading" v-if="slowOutgoingRequests.length === 0" />
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
                            <PulseTh class="text-left">URI</PulseTh>
                            <PulseTh class="text-right">Count</PulseTh>
                            <PulseTh class="text-right">Slowest</PulseTh>
                        </tr>
                    </PulseThead>
                    <tbody class="">
                        <template v-for="(slowRequest, index) in slowOutgoingRequests.slice(0, 100)" :key="index">
                            <tr class="h-2 first:h-0"></tr>
                            <tr>
                                <PulseTd>
                                    <PulseHttpMethodBadge :method="slowRequest.method" />
                                </PulseTd>
                                <PulseTd class="max-w-px">
                                    <div class="flex items-center" :title="slowRequest.uri">
                                        <img
                                            v-if="getDomainFromUrl(slowRequest.uri)"
                                            :src="`https://unavatar.io/${getDomainFromUrl(slowRequest.uri)}?fallback=false`"
                                            loading="lazy"
                                            class="w-4 h-4 mr-2"
                                            onerror="this.style.display='none'"
                                            alt="URL favicon"
                                        />
                                        <code class="block text-xs text-gray-900 dark:text-gray-100 truncate">
                                            {{ slowRequest.uri }}
                                        </code>
                                    </div>
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
                <div v-if="slowOutgoingRequests.length > 100" class="mt-2 text-xs text-neutral-400 text-center">Limited to 100 entries</div>
            </div>
        </PulseScroll>
    </DashboardCard>
</template>
