<script setup lang="ts">
import type { PulseResponse } from '@/types/pulseTypes';

import { format_number, periodForHumans } from '@/service/pulseUtil';
import { computed } from 'vue';

import IconInformationCircle from '@/components/icons/IconInformationCircle.vue';
import IconRocketLaunch from '@/components/icons/IconRocketLaunch.vue';
import PulseNoResults from '@/components/pulse/PulseNoResults.vue';
import DashboardCard from '@/components/cards/DashboardCard.vue';
import PulseScroll from '@/components/pulse/PulseScroll.vue';
import PulseTable from '@/components/pulse/PulseTable.vue';
import PulseThead from '@/components/pulse/PulseThead.vue';
import PulseTd from '@/components/pulse/PulseTd.vue';
import PulseTh from '@/components/pulse/PulseTh.vue';

const validPeriods = ['1_hour', '6_hours', '24_hours', '7_days'];

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

const cacheKeyInteractions = computed(() => {
    return props.pulseData.cache?.cacheKeyInteractions ?? [];
});

const config = computed(() => {
    return props.pulseData.cache.config;
});
</script>
<template>
    <DashboardCard
        :rows="rows"
        :cols="cols"
        name="Cache"
        :title="`Time: ${format_number(pulseData.slow_outgoing_requests?.time ?? 0)}ms; Run at: ${pulseData.slow_outgoing_requests?.runAt ? new Date(pulseData.slow_outgoing_requests?.runAt).toLocaleDateString() : ''};`"
        :details="`past ${validPeriods.indexOf(period) !== -1 ? periodForHumans(period) : periodForHumans(validPeriods[0])}`"
    >
        <template #icon>
            <IconRocketLaunch />
        </template>

        <template #actions>
            <button
                :title="`Keys may be normalized using groups.\n\nThere ${Object.keys(config.groups).length === 1 ? 'is' : 'are'} currently ${Object.keys(config.groups).length} ${Object.keys(config.groups).length === 1 ? 'group' : 'groups'} configured.`"
            >
                <IconInformationCircle class="w-5 h-5 stroke-gray-400 dark:stroke-gray-600" />
            </button>
        </template>

        <PulseScroll :expand="false" :loading="isLoading ?? false">
            <PulseNoResults :isLoading="isLoading" v-if="pulseData.cache.allCacheInteractions.hits === 0 && pulseData.cache.allCacheInteractions.misses === 0" />
            <div class="flex flex-col gap-6" v-else>
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="flex flex-col justify-center @sm:block">
                        <span class="text-xl uppercase font-bold text-gray-700 dark:text-gray-300 tabular-nums">
                            <span
                                v-if="config.sample_rate < 1"
                                :title="`Sample rate: ${config.sample_rate}, Raw value: ${format_number(pulseData.cache.allCacheInteractions.hits)}`"
                            >
                                ~{{ format_number(pulseData.cache.allCacheInteractions.hits * (1 / config.sample_rate)) }}
                            </span>
                            <template v-else>
                                {{ format_number(pulseData.cache.allCacheInteractions.hits) }}
                            </template>
                        </span>
                        <span class="text-xs uppercase font-bold text-gray-500 dark:text-gray-400"> Hits </span>
                    </div>
                    <div class="flex flex-col justify-center @sm:block">
                        <span class="text-xl uppercase font-bold text-gray-700 dark:text-gray-300 tabular-nums">
                            <span
                                v-if="config.sample_rate < 1"
                                :title="`Sample rate: ${config.sample_rate}, Raw value: ${format_number(pulseData.cache.allCacheInteractions.misses)}`"
                            >
                                ~{{ format_number(pulseData.cache.allCacheInteractions.misses * (1 / config.sample_rate)) }}
                            </span>
                            <template v-else>
                                {{ format_number(pulseData.cache.allCacheInteractions.misses) }}
                            </template>
                        </span>
                        <span class="text-xs uppercase font-bold text-gray-500 dark:text-gray-400"> Misses </span>
                    </div>
                    <div class="flex flex-col justify-center @sm:block">
                        <span class="text-xl uppercase font-bold text-gray-700 dark:text-gray-300 tabular-nums">
                            {{
                                Math.floor(
                                    (pulseData.cache.allCacheInteractions.hits /
                                        (parseFloat(`${pulseData.cache.allCacheInteractions.hits}`) + parseFloat(`${pulseData.cache.allCacheInteractions.misses}`))) *
                                        10000,
                                ) / 100
                            }}%
                        </span>
                        <span class="text-xs uppercase font-bold text-gray-500 dark:text-gray-400"> Hit Rate </span>
                    </div>
                </div>
                <div>
                    <PulseTable>
                        <colgroup>
                            <col class="w-full" />
                            <col class="w-0" />
                            <col class="w-0" />
                            <col class="w-0" />
                        </colgroup>
                        <PulseThead>
                            <tr>
                                <PulseTh class="text-left">Key</PulseTh>
                                <PulseTh class="text-right">Hits</PulseTh>
                                <PulseTh class="text-right">Misses</PulseTh>
                                <PulseTh class="text-right whitespace-nowrap">Hit Rate</PulseTh>
                            </tr>
                        </PulseThead>
                        <tbody class="">
                            <template v-for="(interaction, index) in cacheKeyInteractions.slice(0, 100)" :key="index">
                                <tr class="h-2 first:h-0"></tr>
                                <tr>
                                    <PulseTd class="max-w-[1px]">
                                        <code class="block text-xs text-gray-900 dark:text-gray-100 truncate" :title="interaction.key">
                                            {{ interaction.key }}
                                        </code>
                                    </PulseTd>

                                    <PulseTd :numeric="true" class="text-neutral-700 dark:text-neutral-300 font-bold">
                                        <span v-if="config.sample_rate < 1" :title="`Sample rate: ${config.sample_rate}, Raw value: ${format_number(interaction.hits)}`">
                                            ~{{ format_number(interaction.hits * (1 / config.sample_rate)) }}
                                        </span>
                                        <template v-else>
                                            {{ format_number(interaction.hits) }}
                                        </template>
                                    </PulseTd>

                                    <PulseTd :numeric="true" class="text-neutral-700 dark:text-neutral-300 font-bold">
                                        <span v-if="config.sample_rate < 1" :title="`Sample rate: ${config.sample_rate}, Raw value: ${format_number(interaction.misses)}`">
                                            ~{{ format_number(interaction.misses * (1 / config.sample_rate)) }}
                                        </span>
                                        <template v-else>
                                            {{ format_number(interaction.misses) }}
                                        </template>
                                    </PulseTd>

                                    <PulseTd :numeric="true" class="text-gray-700 dark:text-gray-300 font-bold">
                                        {{ Math.floor((interaction.hits / (parseFloat(`${interaction.hits}`) + parseFloat(`${interaction.misses}`))) * 10000) / 100 }}%
                                    </PulseTd>
                                </tr>
                            </template>
                        </tbody>
                    </PulseTable>
                    <div v-if="cacheKeyInteractions.length > 100" class="mt-2 text-xs text-neutral-400 text-center">Limited to 100 entries</div>
                </div>
            </div>
        </PulseScroll>
    </DashboardCard>
</template>
