<script setup lang="ts">
import type { SubTaskResource } from '@/types/resources';
import type { TaskStatus } from '@/types/types';

import { toFormattedDate, toFormattedDuration, toTimeSpan, within24Hrs } from '@/service/util';
import { ref } from 'vue';

import ButtonCorner from '@/components/inputs/ButtonCorner.vue';
import ButtonText from '@/components/inputs/ButtonText.vue';
import Popover from '@/components/pinesUI/Popover.vue';
import ChipTag from '@/components/labels/ChipTag.vue';

import ProiconsCommentExclamation from '~icons/proicons/comment-exclamation';
import ProiconsMoreVertical from '~icons/proicons/more-vertical';
import PulseDoughnutChart from '../charts/PulseDoughnutChart.vue';
import ProiconsChevronDown from '~icons/proicons/chevron-down';
import ProiconsArrowSync from '~icons/proicons/arrow-sync';
import ProiconsDelete from '~icons/proicons/delete';
import SubTaskCard from './SubTaskCard.vue';
import ButtonIcon from '../inputs/ButtonIcon.vue';
import ProiconsArrowReply from '~icons/proicons/arrow-reply';

const props = defineProps<{ data: SubTaskResource }>();
</script>

<template>
    <span class="flex text-left rounded-xl ring-1 ring-gray-900/5 dark:text-white shadow w-full divide-gray-300 dark:divide-neutral-400">
        <section
            class="flex ring-1 ring-inset ring-purple-700 rounded-xl shadow-sm w-full bg-white dark:bg-primary-dark-800/70 dark:hover:bg-primary-dark-600 hover:bg-primary-800 p-3 gap-4 items-center flex-1"
        >
            <div class="relative group flex flex-col gap-1 flex-1">
                <div class="flex gap-x-4 gap-y-2 items-center">
                    <h2 class="truncate capitalize group" :title="data.name">
                        {{ data.name }}
                    </h2>
                    <p v-if="data.summary" class="truncate text-neutral-500 dark:text-neutral-400 max-w-64 hidden md:block">
                        {{ data.summary }}
                    </p>
                    <div
                        v-if="(data.summary?.length ?? 0) > 0"
                        class="flex z-30 left-20 bottom-10 absolute opacity-0 group-hover:opacity-100 transition-opacity ease-in-out duration-500 w-1/2 text-sm p-3 bg-white dark:odd:bg-primary-dark-600/70 dark:bg-neutral-800/70 backdrop-blur-lg border dark:border-none rounded-md shadow-md border-neutral-200/70 gap-2 items-center"
                    >
                        <ProiconsCommentExclamation class="h-4 w-4" />
                        <p class="text-wrap">{{ data.summary }}</p>
                    </div>
                </div>
                <div class="flex gap-x-8 gap-y-2 overflow-clip">
                    <h4 class="text-xs text-neutral-500 dark:text-neutral-400 truncate line-clamp-1 capitalize" title="">
                        {{
                            data.ended_at !== null
                                ? `Finished: ${toFormattedDate(new Date(data.ended_at + ' UTC'), true, within24Hrs(data.ended_at + ' UTC') ? { hour: '2-digit', minute: '2-digit' } : undefined)}`
                                : data.started_at !== null
                                  ? `Started: ${toFormattedDate(new Date(data.started_at + ' UTC'), true, within24Hrs(data.started_at + ' UTC') ? { hour: '2-digit', minute: '2-digit' } : undefined)}`
                                  : `Created: ${toFormattedDate(new Date(data.created_at), true, within24Hrs(data.created_at) ? { hour: '2-digit', minute: '2-digit' } : undefined)}`
                        }}
                    </h4>
                    <h4
                        class="text-xs text-neutral-500 dark:text-neutral-400 truncate line-clamp-1 capitalize w-20 hidden sm:block"
                        title="Duration"
                    >
                        Duration: {{ toFormattedDuration(data.duration) }}
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

            <!-- <td>
                <PulseDoughnutChart
                    class="!h-8 !w-8"
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
                                data: [data.sub_tasks_pending, data.sub_tasks_complete, data.sub_tasks_failed],
                                backgroundColor: ['#c084fc30', '#9333ea', '#be123c'],
                                hoverBackgroundColor: ['#c084fc30', '#9333ea', '#e11d48'],
                            },
                        ],
                    }"
                />
            </td> -->

            <div class="flex gap-1 items-center">
                <PulseDoughnutChart
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
                                    100 - data.progress,
                                    data.status === 'processing' || data.status === 'completed' ? data.progress : 0,
                                    data.status === 'failed' ? data.progress : 0,
                                ],
                                backgroundColor: ['#f59e0b', '#9333ea', '#be123c'],
                                hoverBackgroundColor: ['#f59e0b', '#9333ea', '#e11d48'],
                            },
                        ],
                    }"
                />
                <p class="text-left lg:!hidden text-xs">{{ data.progress }}%</p>

                <ChipTag
                    :class="`h-6 shadow-sm`"
                    :colour="` ${
                        data.status === 'pending'
                            ? 'bg-[#e4e4e4] dark:bg-white !text-neutral-900'
                            : data.status === 'processing'
                              ? 'bg-purple-600 dark:bg-purple-700 !text-white'
                              : data.status === 'completed'
                                ? 'bg-[#660099] !text-white'
                                : data.status === 'incomplete' || data.status === 'cancelled'
                                  ? 'bg-amber-500 !text-neutral-900 '
                                  : 'bg-rose-600 dark:bg-rose-700 !text-white'
                    }`"
                    :label="data.status"
                />
                <ButtonCorner
                    disabled
                    positionClasses="w-7 h-7 !p-1 ml-auto"
                    textClasses="hover:text-rose-600 dark:hover:text-rose-500"
                    colourClasses="dark:hover:bg-neutral-900 hover:bg-gray-100 hover:shadow-md"
                    label="Remove Sub Task Record From Server"
                >
                    <template #text> Remove </template>
                    <template #icon> <ProiconsDelete class="h-4 w-4" /></template>
                </ButtonCorner>
                <!-- <Popover
                        popoverClass="!w-40 rounded-lg "
                        :buttonComponent="ButtonCorner"
                        :button-attributes="{
                            positionClasses: 'w-7 h-7 !p-1 ml-auto',
                            textClasses: 'hover:text-violet-600 dark:hover:text-violet-500',
                            colourClasses: 'dark:hover:bg-neutral-900 hover:bg-gray-100 hover:shadow-md',
                            label: 'Manage Permissions',
                        }"
                        @click.stop="() => {}"
                    >
                        <template #buttonIcon>
                            <ProiconsMoreVertical class="h-4 w-4" />
                        </template>
                        <template #content>
                            <div class="grid gap-4">
                                <div class="space-y-2">
                                    <h4 class="font-medium leading-none">Manage Task</h4>
                                </div>

                                <div class="grid gap-2">
                                    <ButtonText class="h-8 dark:!bg-neutral-950" :title="'Scan for Folder Changes'" :to="'/profile'">
                                        <template #text> Run Again </template>
                                        <template #icon> <ProiconsArrowSync class="h-4 w-4" /></template>
                                    </ButtonText>
                                    <ButtonText
                                        v-if="data.status_key >= 0 && data.status_key <= 1"
                                        class="h-8 text-rose-600 dark:!bg-rose-700 disabled:opacity-60"
                                        :title="'Remove User From Server'"
                                        @click.stop.prevent="$emit('clickAction')"
                                        disabled
                                    >
                                        <template #text> Cancel </template>
                                        <template #icon> <ProiconsDelete class="h-4 w-4" /></template>
                                    </ButtonText>
                                </div>
                            </div>
                        </template>
                    </Popover> -->
            </div>
        </section>
        <ProiconsArrowReply class="-scale-y-100 h-6 w-6 mx-2 my-auto shrink-0" />
    </span>
</template>
