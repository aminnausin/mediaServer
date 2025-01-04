<script setup lang="ts">
import type { PulseResponse } from '@/types/types';

import { useGetPulse, useGetSiteAnalytics } from '@/service/queries';
import { ref, useTemplateRef, watch } from 'vue';
import { handleStartTask } from '@/service/taskService';
import { periodForHumans } from '@/service/util';
import { toast } from '@/service/toaster/toastService';

import PulseRequests from '@/components/pulseCards/PulseRequests.vue';
import DashboardCard from '@/components/cards/DashboardCard.vue';
import PulseServers from '@/components/pulseCards/PulseServers.vue';
import PulseQueues from '@/components/pulseCards/PulseQueues.vue';
import PulseUsage from '@/components/pulseCards/PulseUsage.vue';
import ButtonText from '@/components/inputs/ButtonText.vue';
import Popover from '@/components/pinesUI/Popover.vue';

import LucideChartNoAxesCombined from '~icons/lucide/chart-no-axes-combined';
import LucideFolderSearch from '~icons/lucide/folder-search';
import LucideFolderCheck from '~icons/lucide/folder-check';
import ProiconsArrowSync from '~icons/proicons/arrow-sync';
import LucideFolderTree from '~icons/lucide/folder-tree';
import LucideFolderSync from '~icons/lucide/folder-sync';
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

const { data: stats, isLoading } = useGetSiteAnalytics(period);
const { data: rawPulseData, isLoading: pulseLoading } = useGetPulse({ period });

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
            <div class="flex items-center gap-2 flex-wrap [&>*]:h-8">
                <ButtonText @click.stop.prevent="handleStartTask('scan')">
                    <template #text>Run Full Scan</template>
                    <template #icon><ProiconsArrowSync /></template>
                </ButtonText>
                <Popover popoverClass="!w-52 rounded-lg " :button-attributes="{ title: 'Start New Task' }" ref="taskPopover">
                    <template #buttonText>New Task</template>
                    <template #buttonIcon>
                        <ProiconsAdd />
                    </template>
                    <template #content>
                        <div class="grid gap-4">
                            <div class="space-y-2">
                                <h4 class="font-medium leading-none">Start Server Task</h4>
                            </div>

                            <div class="grid gap-2">
                                <ButtonText
                                    class="h-8 dark:!bg-neutral-950"
                                    :title="'Scan for Folder Changes'"
                                    @click="
                                        handleStartTask('index').then(() => {
                                            taskPopover?.handleClose();
                                        })
                                    "
                                >
                                    <template #text> Index Files </template>
                                    <template #icon> <LucideFolderSearch class="-order-1 h-4 w-4" /></template>
                                </ButtonText>
                                <ButtonText
                                    class="h-8 dark:!bg-neutral-950"
                                    :title="'Sync Folder With Database'"
                                    @click="
                                        handleStartTask('sync').then(() => {
                                            taskPopover?.handleClose();
                                        })
                                    "
                                >
                                    <template #text> Sync Files </template>
                                    <template #icon> <LucideFolderSync class="-order-1 h-4 w-4" /></template>
                                </ButtonText>
                                <ButtonText
                                    class="h-8 dark:!bg-neutral-950 disabled:opacity-60"
                                    :title="'Scan for New Metadata'"
                                    @click="
                                        handleStartTask('verify').then(() => {
                                            taskPopover?.handleClose();
                                        })
                                    "
                                >
                                    <template #text> Verify Metadata </template>
                                    <template #icon> <LucideFolderCheck class="-order-1 h-4 w-4" /></template>
                                </ButtonText>

                                <ButtonText
                                    class="h-8 text-rose-600 dark:!bg-rose-700 disabled:opacity-60"
                                    :title="'Scan and Index All Files For Metadata'"
                                    @click.stop.prevent="handleStartTask('scan').then(() => {
                                            taskPopover?.handleClose();
                                        })"
                                >
                                    <template #text> Scan All Files </template>
                                    <template #icon> <LucideFolderTree class="-order-1 h-4 w-4" /></template>
                                </ButtonText>
                            </div>
                        </div>
                    </template>
                </Popover>
            </div>
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
        </section>
        <PulseServers :pulseData="pulseData" :isLoading="pulseLoading" />
        <span class="mx-auto grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-6 w-full">
            <!-- <StatsCard v-for="stat in stats" :title="`Total ${stat.title}`" :forceCount="stat.count" class="col-span-1 row-span-1 w-full" /> -->
            <DashboardCard cols="2" :rows="4" :title="'Data changes over time'" :name="'Data Changes'" :details="`past ${periodForHumans(period)}`">
                <template #icon>
                    <LucideChartNoAxesCombined class="w-6 h-6" />
                </template>
                <template #slot>
                    <span v-for="(stat, index) in stats?.changes" :key="index" class="flex gap-2 capitalize items-center flex-wrap">
                        <h3 class="text-sm dark:text-slate-400 text-slate-500 w-full text-nowrap">{{ stat.title }}</h3>
                        <span>
                            <h3 class="text-lg">{{ stat.count ?? 0 }}</h3>
                        </span>
                        <h4 :class="`text-sm ${stat.change >= 0 ? 'text-green-700 dark:text-green-600' : 'text-rose-500'} ml-auto`">
                            {{ `${stat.change === 0 ? 'no change' : `${stat.change >= 0 ? '+' : '-'}${stat.change}`}` }}
                        </h4>
                    </span>
                </template>
            </DashboardCard>
            <PulseUsage :pulseData="pulseData" :isLoading="pulseLoading" :period="period" :rows="4" />
            <PulseQueues :pulseData="pulseData" :isLoading="pulseLoading" :period="period" :rows="2" />
            <PulseRequests :pulseData="pulseData" :isLoading="pulseLoading" :period="period" :rows="2" />
        </span>
    </section>
</template>
