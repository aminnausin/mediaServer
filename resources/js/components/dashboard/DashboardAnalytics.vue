<script setup lang="ts">
import type { BreadCrumbItem } from '@/types/types';
import type { PulseResponse } from '@/types/pulseTypes';

import { computed, onMounted, ref, useTemplateRef } from 'vue';
import { ButtonText, ButtonBase } from '@/components/cedar-ui/button';
import { handleStartTask } from '@/service/taskService';
import { BreadCrumbs } from '@/components/cedar-ui/breadcrumbs';
import { BasePopover } from '@/components/cedar-ui/popover';
import { queryClient } from '@/service/vue-query';
import { useGetPulse } from '@/service/queries';
import { useAuth } from '@/composables/auth/useAuth';

import PulseSlowOutgoingRequests from '@/components/cards/pulse/PulseSlowOutgoingRequests.vue';
import DashboardTaskMenu from '@/components/menus/DashboardTaskMenu.vue';
import PulseSlowRequests from '@/components/cards/pulse/PulseSlowRequests.vue';
import PulseSlowQueries from '@/components/cards/pulse/PulseSlowQueries.vue';
import ServerActivityCard from '@/components/cards/server/ServerActivityCard.vue';
import PulseExceptions from '@/components/cards/pulse/PulseExceptions.vue';
import StorageStatsCard from '@/components/cards/server/StorageStatsCard.vue';
import PulseRequests from '@/components/cards/pulse/PulseRequests.vue';
import PulseSlowJobs from '@/components/cards/pulse/PulseSlowJobs.vue';
import PulseServers from '@/components/cards/pulse/PulseServers.vue';
import PulseQueues from '@/components/cards/pulse/PulseQueues.vue';
import PulseUsage from '@/components/cards/pulse/PulseUsage.vue';
import PulseCache from '@/components/cards/pulse/PulseCache.vue';

import ProiconsArrowSync from '~icons/proicons/arrow-sync';
import ProiconsHome2 from '~icons/proicons/home-2';
import ProiconsGraph from '~icons/proicons/graph';
import ProiconsBolt from '~icons/proicons/bolt';
import ProiconsAdd from '~icons/proicons/add';

const validPeriods: { key: string; value: string }[] = [
    { key: '1h', value: '1_hour' },
    { key: '6h', value: '6_hours' },
    { key: '24h', value: '24_hours' },
    { key: '7d', value: '7_days' },
    { key: '30d', value: '30_days' },
];

const taskPopover = useTemplateRef('taskPopover');
const period = ref<string>('1_hour');

const { data: rawPulseData, isLoading: pulseLoading } = useGetPulse({ period });

const { isAdmin } = useAuth();

const pulseData = computed<PulseResponse>(() => rawPulseData.value?.data as PulseResponse);

const breadCrumbs = computed(() => {
    const items: BreadCrumbItem[] = [
        {
            name: 'Dashboard',
            url: '/dashboard',
            icon: ProiconsHome2,
        },
        {
            name: 'Dashboard',
            url: '/dashboard/analytics',
            icon: ProiconsGraph,
        },
    ];

    return items;
});

onMounted(() => {
    queryClient.invalidateQueries({ queryKey: ['pulse', period] });
});
</script>
<template>
    <div class="flex flex-wrap items-center justify-between gap-2">
        <BreadCrumbs :bread-crumbs="breadCrumbs" />
        <div class="ml-auto flex flex-wrap items-center gap-2 text-sm font-medium *:leading-4">
            <h5>Time Period</h5>
            <ButtonBase
                v-for="(validPeriod, index) in validPeriods"
                @click="period = validPeriod.value"
                :key="index"
                :title="`Set analytics period to ${validPeriod.key}`"
                :class="[
                    'p-0 font-semibold tabular-nums hover:text-gray-400 dark:hover:text-gray-500',
                    period === validPeriod.value ? 'text-foreground-1 dark:text-foreground-4' : 'dark:text-foreground-3 text-gray-300',
                ]"
            >
                {{ validPeriod.key }}
            </ButtonBase>
        </div>
        <div class="xs:*:h-8 flex w-full flex-wrap items-center gap-2 *:h-fit">
            <ButtonText @click.stop.prevent="handleStartTask('scan')" text="Run Full Scan" title="Scan All Files" class="xs:flex-initial flex-1">
                <template #icon><ProiconsArrowSync /></template>
            </ButtonText>
            <BasePopover
                ref="taskPopover"
                class="xs:flex-initial flex-1"
                popoverClass="text-xs sm:text-sm max-w-36 sm:max-w-40 p-1"
                :button-component="ButtonText"
                :button-attributes="{ title: 'Start New Task', text: 'New Task', class: 'h-full xs:flex-initial flex-1' }"
            >
                <template #buttonIcon>
                    <ProiconsAdd />
                </template>
                <template #content>
                    <DashboardTaskMenu @handle-close="taskPopover?.handleClose" :show-scan-all="false" />
                </template>
            </BasePopover>
            <ButtonText v-if="isAdmin" href="/pulse" text="Pulse" title="Detailed Analytics" class="xs:flex-initial flex-1">
                <template #icon><ProiconsBolt /></template>
            </ButtonText>
        </div>
    </div>
    <span class="mx-auto grid w-full grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-6">
        <PulseServers :pulseData="pulseData" :isLoading="pulseLoading" cols="6" :rows="1" />
        <ServerActivityCard :period="period" />
        <PulseUsage :pulseData="pulseData" :isLoading="pulseLoading" :period="period" :rows="1" class="lg:col-span-6 2xl:col-span-2 2xl:col-start-5! 2xl:row-start-3" />
        <StorageStatsCard :rows="1" cols="6" class="lg:col-span-6 2xl:col-span-4" />

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
