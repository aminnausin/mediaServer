<script setup lang="ts">
import type { PulseResponse } from '@/types/pulseTypes';

import { format_number, periodForHumans } from '@/service/pulseUtil';
import { computed, ref } from 'vue';
import { InputSelect } from '@/components/cedar-ui/select';
import { sortObject } from '@/service/sort/baseSort';

import PulseSelectLabel from '@/components/pulse/PulseSelectLabel.vue';
import IconCircleStack from '@/components/icons/IconCircleStack.vue';
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

const slowQueries = computed(() => {
    const list = props.pulseData.slow_queries.slowQueries ?? [];
    const sortKey = sort.value.value as keyof (typeof list)[0];

    return [...list].sort(sortObject(sortKey, -1, []));
});

const config = computed(() => {
    return props.pulseData.slow_queries.config;
});
</script>
<template>
    <DashboardCard
        :rows="rows"
        :cols="cols"
        name="Slow Queries"
        :title="`Time: ${format_number(pulseData.slow_queries.time ?? 0)}ms; Run at: ${pulseData.slow_queries.runAt ? new Date(pulseData.slow_queries.runAt).toLocaleDateString() : ''};`"
        :details="`${config.threshold ?? 1000}ms threshold, past ${validPeriods.includes(period) ? periodForHumans(period) : periodForHumans(validPeriods[0])}`"
    >
        <template #icon>
            <IconCircleStack />
        </template>

        <template #actions>
            <PulseSelectLabel controlId="Select-Slow-Queries-Order"> Sort By </PulseSelectLabel>

            <InputSelect
                :placeholder="'None'"
                :options="exceptionOptions"
                class="w-full! flex-1 rounded-l-none whitespace-nowrap! capitalize ring-inset"
                title="Select usage type"
                @selectItem="handleSetSort"
                :defaultItem="0"
                id="Select-Slow-Queries-Order"
            />
        </template>

        <PulseScroll :expand="false" :loading="isLoading ?? false">
            <div class="flex min-h-full flex-col">
                <PulseNoResults :isLoading="isLoading" v-if="slowQueries.length === 0" />
                <PulseTable v-else>
                    <colgroup>
                        <col class="w-full" />
                        <col class="w-0" />
                        <col class="w-0" />
                    </colgroup>
                    <PulseThead>
                        <tr>
                            <PulseTh class="text-left">Query</PulseTh>
                            <PulseTh class="text-right">Count</PulseTh>
                            <PulseTh class="text-right">Slowest</PulseTh>
                        </tr>
                    </PulseThead>
                    <tbody class="">
                        <template v-for="(query, index) in slowQueries.slice(0, 100)" :key="index">
                            <tr class="h-2 first:h-0"></tr>
                            <tr>
                                <PulseTd class="max-w-px truncate p-0!">
                                    <div class="relative">
                                        <div
                                            class="block overflow-x-auto rounded-md bg-gray-700 py-4 text-xs whitespace-nowrap text-gray-100 [scrollbar-color:var(--color-gray-500)_transparent] [scrollbar-width:thin] dark:bg-gray-800"
                                        >
                                            <code class="px-3 whitespace-nowrap" v-html="query.sql"></code>
                                            <p v-if="query.location" class="mt-3 px-3 leading-none text-gray-400 dark:text-gray-500">
                                                {{ query.location }}
                                            </p>
                                            <p v-if="Array.isArray(config?.threshold)" class="mt-3 px-3 leading-none text-gray-400 dark:text-gray-500">
                                                {{ query.threshold }}ms threshold
                                            </p>
                                        </div>
                                        <div
                                            class="pointer-events-none absolute top-0 right-0 bottom-0 w-3 rounded-r-md bg-linear-to-r from-transparent to-gray-700 dark:to-gray-800"
                                        ></div>
                                    </div>
                                </PulseTd>
                                <PulseTd :numeric="true" class="text-foreground-6 font-bold">
                                    <span v-if="config.sample_rate < 1" :title="`Sample rate: ${config.sample_rate}, Raw value: ${format_number(query.count)}`">
                                        ~{{ format_number(query.count * (1 / config.sample_rate)) }}
                                    </span>
                                    <template v-else>
                                        {{ format_number(query.count) }}
                                    </template>
                                </PulseTd>
                                <PulseTd :numeric="true" class="text-foreground-6">
                                    <strong v-if="!query.slowest">Unknown</strong>
                                    <template v-else>
                                        <strong>{{ format_number(query.slowest) ?? '<1' }}</strong> ms
                                    </template>
                                </PulseTd>
                            </tr>
                        </template>
                    </tbody>
                </PulseTable>
                <div v-if="slowQueries.length > 100" class="mt-2 text-center text-xs text-neutral-400">Limited to 100 entries</div>
            </div>
        </PulseScroll>
    </DashboardCard>
</template>

@php use \Doctrine\SqlFormatter\HtmlHighlighter; use \Doctrine\SqlFormatter\SqlFormatter; if ($this->wantsHighlighting()) { $sqlFormatter = new SqlFormatter(new HtmlHighlighter([
HtmlHighlighter::HIGHLIGHT_RESERVED => 'class="font-semibold"', HtmlHighlighter::HIGHLIGHT_QUOTE => 'class="text-purple-200"', HtmlHighlighter::HIGHLIGHT_BACKTICK_QUOTE =>
'class="text-purple-200"', HtmlHighlighter::HIGHLIGHT_BOUNDARY => 'class="text-cyan-200"', HtmlHighlighter::HIGHLIGHT_NUMBER => 'class="text-orange-200"',
HtmlHighlighter::HIGHLIGHT_WORD => 'class="text-orange-200"', HtmlHighlighter::HIGHLIGHT_VARIABLE => 'class="text-orange-200"', HtmlHighlighter::HIGHLIGHT_ERROR =>
'class="text-red-200"', HtmlHighlighter::HIGHLIGHT_COMMENT => 'class="text-gray-400"', ], false)); } @endphp
