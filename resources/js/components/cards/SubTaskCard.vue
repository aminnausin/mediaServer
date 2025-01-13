<script setup lang="ts">
import type { SubTaskResource } from '@/types/resources';

import { toFormattedDate, toFormattedDuration, toTimeSpan, within24Hrs } from '@/service/util';

import ButtonCorner from '@/components/inputs/ButtonCorner.vue';
import HoverCard from '@/components/cards/HoverCard.vue';
import ChipTag from '@/components/labels/ChipTag.vue';

import PulseDoughnutChart from '../charts/PulseDoughnutChart.vue';
import ProiconsDelete from '~icons/proicons/delete';
import ProiconsArrowReply from '~icons/proicons/arrow-reply';

const props = defineProps<{ data: SubTaskResource; isScreenLarge?: boolean }>();
const emit = defineEmits(['clickAction']);
</script>

<template>
    <span class="flex text-left rounded-xl dark:text-white w-full">
        <section
            class="flex flex-wrap flex-1 ring-1 truncate ring-inset ring-gray-900/5 hover:ring-purple-700 rounded-md shadow-sm w-full bg-white dark:bg-primary-dark-800/70 dark:hover:bg-primary-dark-600 hover:bg-primary-800 p-3 gap-4 items-center"
        >
            <div class="relative group flex flex-col gap-1 flex-1 truncate">
                <HoverCard :content="data.summary ?? data.name ?? ''" class="flex gap-x-4 gap-y-2 items-center truncate">
                    <template #trigger>
                        <h2 class="truncate capitalize group">{{ data.id }} - {{ data.name }}</h2>
                        <p v-if="data.summary" class="truncate text-neutral-500 dark:text-neutral-400 max-w-48 lg:max-w-20 xl:max-w-64 hidden md:block">
                            {{ data.summary }}
                        </p>
                    </template>
                </HoverCard>
                <div class="flex gap-x-8 gap-y-2 p">
                    <h4
                        class="text-xs text-neutral-500 dark:text-neutral-400 truncate line-clamp-1 capitalize"
                        :title="
                            `Created: ${data.created_at}\n` +
                            (data.started_at ? `Started: ${data.started_at} UTC\n` : '') +
                            (data.ended_at ? `Finished: ${data.ended_at} UTC\n` : '')
                        "
                    >
                        {{
                            data.ended_at !== null
                                ? `Finished: ${toFormattedDate(new Date(data.ended_at + ' UTC'), true, within24Hrs(data.ended_at + ' UTC') ? { hour: '2-digit', minute: '2-digit' } : undefined)}`
                                : data.started_at !== null
                                  ? `Started: ${toFormattedDate(new Date(data.started_at + ' UTC'), true, within24Hrs(data.started_at + ' UTC') ? { hour: '2-digit', minute: '2-digit' } : undefined)}`
                                  : `Created: ${toFormattedDate(new Date(data.created_at), true, within24Hrs(data.created_at) ? { hour: '2-digit', minute: '2-digit' } : undefined)}`
                        }}
                    </h4>
                    <h4 class="text-xs text-neutral-500 dark:text-neutral-400 truncate line-clamp-1 capitalize w-20 lg:w-fit hidden sm:block" title="Duration">
                        {{ data.duration || data.ended_at ? 'Duration:' : data.started_at ? 'Started: ' : 'Scheduled: ' }}
                        {{ data.duration || data.ended_at ? toFormattedDuration(data.duration, false) : toTimeSpan(data.started_at ?? data.created_at, ' UTC') }}
                    </h4>
                </div>
            </div>

            <div class="px-2 text-xs hidden lg:flex flex-col gap-1 h-fit min-w-32">
                <p class="w-full text-left pe-8">{{ data.progress }}% Processed</p>
                <div class="rounded-full h-1 w-full bg-primary-dark-900 flex overflow-clip">
                    <span
                        :class="`h-1 ${data.status === 'completed' ? 'bg-purple-600' : data.status === 'failed' ? 'bg-rose-600' : 'bg-amber-500'} rounded-full`"
                        :style="`width: ${data.progress}%;`"
                    ></span>
                </div>
            </div>

            <div class="flex gap-1 items-center shrink-0 sm:flex-none">
                <PulseDoughnutChart
                    v-if="isScreenLarge ?? false"
                    v-cloak
                    class="!h-6 !w-6 lg:!hidden"
                    :chart-options="{
                        borderWidth: 0,
                        plugins: {
                            legend: {
                                display: false,
                            },
                            tooltip: {
                                enabled: false,
                                displayColors: false,
                            },
                        },
                    }"
                    :chart-data="{
                        labels: ['Pending', 'Completed', 'Failed'],
                        datasets: [
                            {
                                data: [
                                    Math.max(100 - data.progress, 0),
                                    data.status === 'processing' || data.status === 'completed' ? Math.max(data.progress, 0) : 0,
                                    data.status === 'failed' ? Math.max(data.progress, 0) : 0,
                                ],
                                backgroundColor: ['#f59e0b', '#9333ea', '#be123c'],
                                hoverBackgroundColor: ['#f59e0b', '#9333ea', '#e11d48'],
                            },
                        ],
                    }"
                />
                <p class="text-left lg:!hidden text-xs flex-1">{{ Math.max(data.progress, 0) }}%</p>

                <ChipTag
                    :class="`h-6 shadow-sm`"
                    :colour="`${data.status === 'pending' ? 'bg-[#e4e4e4] dark:bg-white !text-neutral-900' : '!text-white'} ${
                        data.status === 'processing'
                            ? 'bg-purple-600 dark:bg-purple-700'
                            : data.status === 'completed'
                              ? 'bg-[#660099] '
                              : data.status === 'incomplete' || data.status === 'cancelled'
                                ? 'bg-amber-500 !text-neutral-900 '
                                : 'bg-rose-600 dark:bg-rose-700 '
                    }`"
                    :label="data.status"
                />
                <ButtonCorner
                    :disabled="data.status === 'pending' || data.status === 'processing'"
                    positionClasses="w-7 h-7 !p-1 ml-auto"
                    textClasses="hover:text-rose-600 dark:hover:text-rose-500"
                    colourClasses="dark:hover:bg-neutral-900 hover:bg-gray-100 hover:shadow-md"
                    label="Remove Sub Task Record From Server"
                    @click="$emit('clickAction', 'subTask')"
                >
                    <template #text> Remove </template>
                    <template #icon> <ProiconsDelete class="h-4 w-4" /></template>
                </ButtonCorner>
            </div>
        </section>
        <ProiconsArrowReply class="hidden sm:block -scale-y-100 h-6 w-6 mx-2 my-auto shrink-0" />
    </span>
</template>
