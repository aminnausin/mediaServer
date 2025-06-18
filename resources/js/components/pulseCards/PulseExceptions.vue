<script setup lang="ts">
import type { PulseResponse } from '@/types/pulseTypes';

import { format_number, periodForHumans } from '@/service/pulseUtil';
import { computed, ref } from 'vue';
import { sortObject } from '@/service/sort/baseSort';
import { toTimeSpan } from '@/service/util';

import PulseNoResults from '@/components/pulse/PulseNoResults.vue';
import DashboardCard from '@/components/cards/DashboardCard.vue';
import PulseScroll from '@/components/pulse/PulseScroll.vue';
import InputSelect from '@/components/pinesUI/InputSelect.vue';
import IconBugAnt from '@/components/icons/IconBugAnt.vue';
import PulseTable from '@/components/pulse/PulseTable.vue';
import PulseThead from '@/components/pulse/PulseThead.vue';
import PulseTd from '@/components/pulse/PulseTd.vue';
import PulseTh from '@/components/pulse/PulseTh.vue';

const validPeriods = ['1_hour', '6_hours', '24_hours', '7_days'];

const exceptionOptions = [
    { value: 'count', title: 'count' },
    { value: 'latest', title: 'latest' },
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
        cols: 'full',
        rows: 4,
    },
);

const sort = ref<{ value: 'count' | 'latest'; title: string }>({
    value: 'count',
    title: 'count',
});

const handleSetSort = (newSortKey: { value: 'count' | 'latest'; title: string }) => {
    sort.value = newSortKey;
};

const exceptions = computed(() => {
    const list = props.pulseData?.exceptions?.exceptions ?? [];
    const sortKey = sort.value.value as keyof (typeof list)[0];

    return [...list]
        .map((val) => {
            return { ...val, count: parseFloat(val.count) };
        })
        .sort(sortObject(sortKey, sortKey === 'count' ? -1 : 1, ['latest']));
});
</script>
<template>
    <DashboardCard
        :rows="rows"
        :cols="cols"
        :class="props.class"
        name="Exceptions"
        :title="`Time: ${format_number(pulseData?.exceptions?.time ?? 0)}ms; Run at: ${pulseData?.exceptions?.runAt ? new Date(pulseData?.exceptions?.runAt).toLocaleDateString() : ''};`"
        :details="`past ${validPeriods.indexOf(period) !== -1 ? periodForHumans(period) : periodForHumans(validPeriods[0])}`"
    >
        <template #icon>
            <IconBugAnt />
        </template>

        <template #actions>
            <label
                class="capitalize whitespace-nowrap h-10 flex items-center justify-between w-full py-2 p-3 text-left rounded-l-md shadow-sm text-sm ring-inset ring-1 ring-neutral-200 dark:ring-neutral-700 text-gray-900 dark:text-neutral-100 bg-primary-800 dark:bg-neutral-900"
                >Sort By</label
            >
            <InputSelect
                :placeholder="'None'"
                :options="exceptionOptions"
                class="flex-1 rounded-l-none capitalize !w-full !whitespace-nowrap"
                title="Select usage type"
                @selectItem="handleSetSort"
                :defaultItem="0"
            />
        </template>

        <PulseScroll :expand="false" :loading="isLoading ?? false">
            <div class="min-h-full flex flex-col">
                <PulseNoResults v-if="exceptions.length === 0" />
                <PulseTable v-else>
                    <colgroup>
                        <col width="100%" />
                        <col width="0%" />
                        <col width="0%" />
                    </colgroup>
                    <PulseThead>
                        <tr>
                            <PulseTh class="text-left">Type</PulseTh>
                            <PulseTh class="text-right">Latest</PulseTh>
                            <PulseTh class="text-right">Count</PulseTh>
                        </tr>
                    </PulseThead>
                    <tbody class="">
                        <template v-for="(exception, index) in exceptions.slice(0, 100)" :key="index">
                            <tr class="h-2 first:h-0"></tr>
                            <tr>
                                <PulseTd class="max-w-[1px]">
                                    <code class="block text-xs text-neutral-900 dark:text-neutral-100 truncate" :title="exception.class">
                                        {{ exception.class }}
                                    </code>
                                    <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400 truncate" :title="exception.location">
                                        {{ exception.location }}
                                    </p>
                                </PulseTd>
                                <PulseTd :numeric="true" class="text-neutral-700 dark:text-neutral-300 font-bold">
                                    {{ toTimeSpan(new Date(exception.latest), ' EST', true) }}
                                </PulseTd>
                                <PulseTd :numeric="true" class="text-neutral-700 dark:text-neutral-300 font-bold">
                                    <span
                                        v-if="pulseData?.exceptions?.config.sample_rate && pulseData.exceptions.config.sample_rate < 1"
                                        :title="`Sample rate: ${pulseData?.exceptions?.config.sample_rate}, Raw value: ${format_number(exception.count)}`"
                                        >~{{ format_number(exception.count * (1 / pulseData.exceptions.config.sample_rate)) }}</span
                                    >
                                    <template v-else>
                                        {{ format_number(exception.count) }}
                                    </template>
                                </PulseTd>
                            </tr>
                        </template>
                    </tbody>
                </PulseTable>
                <div v-if="exceptions.length > 100" class="mt-2 text-xs text-neutral-400 text-center">Limited to 100 entries</div>
            </div>
        </PulseScroll>
    </DashboardCard>
</template>
