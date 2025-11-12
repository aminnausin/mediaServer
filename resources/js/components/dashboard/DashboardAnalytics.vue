<script setup lang="ts">
import type { BreadCrumbItem } from '@/types/types';
import type { PulseResponse } from '@/types/pulseTypes';

import { computed, ref, useTemplateRef, watch } from 'vue';
import { useGetPulse, useGetSiteAnalytics } from '@/service/queries';
import { handleStartTask } from '@/service/taskService';
import { periodForHumans } from '@/service/pulseUtil';

import PulseSlowOutgoingRequests from '@/components/pulseCards/PulseSlowOutgoingRequests.vue';
import LucideChartNoAxesCombined from '~icons/lucide/chart-no-axes-combined';
import PulseSlowRequests from '@/components/pulseCards/PulseSlowRequests.vue';
import DashboardTaskMenu from '@/components/dashboard/DashboardTaskMenu.vue';
import PulseSlowQueries from '@/components/pulseCards/PulseSlowQueries.vue';
import PulseExceptions from '@/components/pulseCards/PulseExceptions.vue';
import PulseRequests from '@/components/pulseCards/PulseRequests.vue';
import PulseSlowJobs from '@/components/pulseCards/PulseSlowJobs.vue';
import DashboardCard from '@/components/cards/DashboardCard.vue';
import PulseServers from '@/components/pulseCards/PulseServers.vue';
import PulseQueues from '@/components/pulseCards/PulseQueues.vue';
import BreadCrumbs from '@/components/pinesUI/BreadCrumbs.vue';
import BasePopover from '@/components/pinesUI/BasePopover.vue';
import PulseUsage from '@/components/pulseCards/PulseUsage.vue';
import ButtonText from '@/components/inputs/ButtonText.vue';
import PulseCache from '@/components/pulseCards/PulseCache.vue';

import ProiconsArrowSync from '~icons/proicons/arrow-sync';
import ProiconsHome2 from '~icons/proicons/home-2';
import ProiconsBolt from '~icons/proicons/bolt';
import ProiconsAdd from '~icons/proicons/add';

const validPeriods: { key: string; value: string }[] = [
    { key: '1h', value: '1_hour' },
    { key: '6h', value: '6_hours' },
    { key: '24h', value: '24_hours' },
    { key: '7d', value: '7_days' },
    { key: '30d', value: '30_days' },
];

const period = ref<string>('1_hour');
const pulseData = ref<PulseResponse>();
const taskPopover = useTemplateRef('taskPopover');

const { data: stats } = useGetSiteAnalytics(period);
const { data: rawPulseData, isLoading: pulseLoading } = useGetPulse({ period });

const breadCrumbs = computed(() => {
    const items: BreadCrumbItem[] = [
        {
            name: 'Dashboard',
            url: '/dashboard/analytics',
            icon: ProiconsHome2,
        },
    ];

    return items;
});

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
    <div class="flex items-center gap-2 justify-between flex-wrap">
        <BreadCrumbs :bread-crumbs="breadCrumbs" />

        <div class="flex items-center flex-wrap gap-2 ml-auto text-sm font-medium">
            <h5>Time Period</h5>
            <button
                v-for="(validPeriod, index) in validPeriods"
                :key="index"
                @click="period = validPeriod.value"
                :class="`font-semibold ${period === validPeriod.value ? 'text-gray-700 dark:text-gray-300' : 'text-gray-300 dark:text-gray-600'}  hover:text-gray-400 dark:hover:text-gray-500`"
            >
                {{ validPeriod.key }}
            </button>
        </div>
        <div class="flex flex-wrap items-center gap-2 *:h-fit xs:*:h-8 w-full">
            <ButtonText @click.stop.prevent="handleStartTask('scan')" :text="'Run Full Scan'" title="Scan All Files" class="flex-1 xs:flex-initial">
                <template #icon><ProiconsArrowSync /></template>
            </ButtonText>
            <BasePopover popoverClass="w-52! rounded-lg mt-10 " :button-attributes="{ title: 'Start New Task', text: 'New Task', class: 'h-full' }" ref="taskPopover" class="">
                <template #buttonIcon>
                    <ProiconsAdd />
                </template>
                <template #content>
                    <DashboardTaskMenu @handle-close="taskPopover?.handleClose" :show-scan-all="false" />
                </template>
            </BasePopover>
            <ButtonText :to="'/pulse'" text="Pulse" title="Detailed Analytics" class="flex-1 xs:flex-initial">
                <template #icon><ProiconsBolt /></template>
            </ButtonText>
        </div>
    </div>
    <span class="mx-auto grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-6 w-full">
        <PulseServers :pulseData="pulseData" :isLoading="pulseLoading" cols="6" :rows="1" />
        <DashboardCard cols="2" :rows="4" :title="'Data changes over time'" :name="'Data Changes'" :details="`past ${periodForHumans(period)}`">
            <template #icon>
                <LucideChartNoAxesCombined class="w-6 h-6" />
            </template>
            <span v-for="(stat, index) in stats?.changes" :key="index" class="flex gap-2 capitalize items-center flex-wrap">
                <h3 class="text-sm dark:text-neutral-400 text-neutral-500 w-full text-nowrap">{{ stat.title }}</h3>
                <span>
                    <h3 class="text-lg">{{ stat.count ?? 0 }}</h3>
                </span>
                <h4 :class="`text-sm ${stat.change >= 0 ? 'text-green-700 dark:text-green-600' : 'text-rose-500'} ml-auto`">
                    {{ `${stat.change === 0 ? 'no change' : `${stat.change >= 0 ? '+' : '-'}${stat.change}`}` }}
                </h4>
            </span>
        </DashboardCard>
        <PulseUsage :pulseData="pulseData" :isLoading="pulseLoading" :period="period" :rows="4" />
        <PulseQueues :pulseData="pulseData" :isLoading="pulseLoading" :period="period" :rows="2" />
        <PulseRequests v-if="pulseData" :pulseData="pulseData" :isLoading="pulseLoading" :period="period" :rows="2" />
        <PulseSlowRequests v-if="pulseData" :pulse-data="pulseData" :is-loading="pulseLoading" :period="period" :rows="2" cols="3" />
        <PulseSlowJobs v-if="pulseData" :pulse-data="pulseData" :is-loading="pulseLoading" :period="period" :rows="2" :cols="3" />
        <PulseSlowQueries v-if="pulseData" :pulse-data="pulseData" :is-loading="pulseLoading" :period="period" :rows="2" :cols="3" />
        <PulseCache v-if="pulseData" :pulse-data="pulseData" :is-loading="pulseLoading" :period="period" :rows="2" :cols="3" />
        <PulseExceptions :pulse-data="pulseData" :is-loading="pulseLoading" :period="period" :rows="2" :cols="3" />
        <PulseSlowOutgoingRequests v-if="pulseData" :pulse-data="pulseData" :is-loading="pulseLoading" :period="period" :rows="2" :cols="3" />
    </span>
</template>
