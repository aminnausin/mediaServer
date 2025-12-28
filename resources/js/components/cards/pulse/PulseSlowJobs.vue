<script setup lang="ts">
import type { PulseResponse } from '@/types/pulseTypes';

import { format_number, periodForHumans } from '@/service/pulseUtil';
import { computed, ref } from 'vue';
import { InputSelect } from '@/components/cedar-ui/select';
import { sortObject } from '@/service/sort/baseSort';

import PulseSelectLabel from '@/components/pulse/PulseSelectLabel.vue';
import IconCommandLine from '@/components/icons/IconCommandLine.vue';
import PulseNoResults from '@/components/pulse/PulseNoResults.vue';
import DashboardCard from '@/components/cards/layout/DashboardCard.vue';
import PulseScroll from '@/components/pulse/PulseScroll.vue';
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

const slowJobs = computed(() => {
    const list = props.pulseData.slow_jobs.slowJobs ?? [];
    const sortKey = sort.value.value as keyof (typeof list)[0];

    return [...list].sort(sortObject(sortKey, -1, []));
});

const config = computed(() => {
    return props.pulseData.slow_jobs.config;
});
</script>
<template>
    <DashboardCard
        :rows="rows"
        :cols="cols"
        name="Slow Jobs"
        :title="`Time: ${format_number(pulseData.slow_jobs.time ?? 0)}ms; Run at: ${pulseData.slow_jobs.runAt ? new Date(pulseData.slow_jobs.runAt).toLocaleDateString() : ''};`"
        :details="`${config.threshold ?? 1000}ms threshold, past ${validPeriods.indexOf(period) !== -1 ? periodForHumans(period) : periodForHumans(validPeriods[0])}`"
    >
        <template #icon>
            <IconCommandLine />
        </template>

        <template #actions>
            <PulseSelectLabel controlId="Select-Slow-Jobs-Order"> Sort By </PulseSelectLabel>
            <InputSelect
                :placeholder="'None'"
                :options="exceptionOptions"
                class="w-full! flex-1 rounded-l-none whitespace-nowrap! capitalize ring-inset"
                title="Select usage type"
                @selectItem="handleSetSort"
                :defaultItem="0"
                id="Select-Slow-Jobs-Order"
            />
        </template>

        <PulseScroll :expand="false" :loading="isLoading ?? false">
            <div class="flex min-h-full flex-col">
                <PulseNoResults :isLoading="isLoading" v-if="slowJobs.length === 0" />
                <PulseTable v-else>
                    <colgroup>
                        <col class="w-full" />
                        <col class="w-0" />
                        <col class="w-0" />
                    </colgroup>
                    <PulseThead>
                        <tr>
                            <PulseTh class="text-left">job</PulseTh>
                            <PulseTh class="text-right">Count</PulseTh>
                            <PulseTh class="text-right">Slowest</PulseTh>
                        </tr>
                    </PulseThead>
                    <tbody class="">
                        <template v-for="(slowJob, index) in slowJobs.slice(0, 100)" :key="index">
                            <tr class="h-2 first:h-0"></tr>
                            <tr>
                                <PulseTd class="max-w-px text-xs">
                                    <code class="text-foreground-0 block truncate" :title="slowJob.job">
                                        {{ slowJob.job }}
                                    </code>
                                    <p v-if="Array.isArray(config?.threshold)" class="text-foreground-2 mt-1">{{ slowJob.threshold }}ms threshold</p>
                                </PulseTd>
                                <PulseTd :numeric="true" class="text-foreground-6 font-bold">
                                    <span v-if="config.sample_rate < 1" :title="`Sample rate: ${config.sample_rate}, Raw value: ${format_number(slowJob.count)}`"
                                        >~{{ format_number(slowJob.count * (1 / config.sample_rate)) }}</span
                                    >
                                    <template v-else>
                                        {{ format_number(slowJob.count) }}
                                    </template>
                                </PulseTd>
                                <PulseTd :numeric="true" class="text-foreground-6">
                                    <strong v-if="!slowJob.slowest">Unknown</strong>
                                    <template v-else>
                                        <strong>{{ format_number(slowJob.slowest) ?? '<1' }}</strong> ms
                                    </template>
                                </PulseTd>
                            </tr>
                        </template>
                    </tbody>
                </PulseTable>
                <div v-if="slowJobs.length > 100" class="mt-2 text-center text-xs text-neutral-400">Limited to 100 entries</div>
            </div>
        </PulseScroll>
    </DashboardCard>
</template>
