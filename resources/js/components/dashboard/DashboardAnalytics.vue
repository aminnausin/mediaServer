<script setup lang="ts">
import type { PulseResponse } from '@/types/types';

import { useGetPulse, useGetSiteAnalytics } from '@/service/queries';
import { ref, watch } from 'vue';

import PulseServers from '@/components/pulseCards/PulseServers.vue';

import LucideChartNoAxesCombined from '~icons/lucide/chart-no-axes-combined';
import PulseQueues from '../pulseCards/PulseQueues.vue';
import DashboardCard from '../cards/DashboardCard.vue';
import { periodForHumans } from '@/service/util';

const validPeriods: { key: string; value: string }[] = [
    { key: '1h', value: '1_hour' },
    { key: '6h', value: '6_hours' },
    { key: '24h', value: '24_hours' },
    { key: '7d', value: '7_days' },
    { key: '30d', value: '30_days' },
];

const period = ref<string>('1_hour');

const { data: stats, isLoading } = useGetSiteAnalytics(period);

const { data: rawPulseData, isLoading: pulseLoading } = useGetPulse({ period });

const pulseData = ref<PulseResponse>();
const setPeriod = (newPeriod: string) => {
    period.value = newPeriod;
};

watch(
    () => rawPulseData.value,
    () => {
        if (rawPulseData?.value?.data) {
            pulseData.value = rawPulseData.value.data;
        }
    },
);
</script>
<template>
    <section class="flex flex-wrap gap-4 flex-col">
        <section class="flex justify-between flex-wrap gap-2">
            <h4>Website Analytics</h4>
            <div class="flex items-center flex-wrap gap-2">
                <h5>Time Period</h5>
                <button
                    v-for="(validPeriod, index) in validPeriods"
                    :key="index"
                    @click="setPeriod(validPeriod.value)"
                    :class="`font-semibold sm:text-lg ${period === validPeriod.value ? 'text-gray-700 dark:text-gray-300' : 'text-gray-300 dark:text-gray-600'}  hover:text-gray-400 dark:hover:text-gray-500`"
                >
                    {{ validPeriod.key }}
                </button>
            </div>
        </section>
        <PulseServers :pulseData="pulseData" :isLoading="pulseLoading" />
        <span class="mx-auto grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-6 w-full">
            <!-- <StatsCard v-for="stat in stats" :title="`Total ${stat.title}`" :forceCount="stat.count" class="col-span-1 row-span-1 w-full" /> -->
            <DashboardCard
                cols="2"
                :rows="4"
                :title="'Data changes over time'"
                :name="'Data Changes'"
                :details="`past ${periodForHumans(period)}`"
            >
                <template #icon>
                    <LucideChartNoAxesCombined class="w-6 h-6 text-gray-400 dark:text-gray-600" />
                </template>
                <template #slot>
                    <span v-for="(stat, index) in stats?.changes" :key="index" class="flex gap-2 capitalize items-center flex-wrap">
                        <h3 class="text-sm dark:text-slate-400 text-slate-500 w-full text-nowrap">{{ stat.title }}</h3>
                        <span>
                            <h3 class="text-lg">{{ stat.count ?? 0 }}</h3>
                        </span>
                        <h4 :class="`text-sm ${stat.change >= 0 ? 'text-green-600' : 'text-red-500'} ml-auto`">
                            {{ `${stat.change === 0 ? 'no change' : `${stat.change >= 0 ? '+' : '-'}${stat.change}`}` }}
                        </h4>
                    </span>
                </template>
            </DashboardCard>
            <PulseQueues :pulseData="pulseData" :isLoading="pulseLoading" :period="period" :cols="4" :rows="2" />
        </span>
    </section>
</template>
