<script setup lang="ts">
import type { TaskResource } from '@/types/resources';

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

const props = defineProps<{ data: TaskResource }>();

const expanded = ref(false);
</script>
<template>
    <div
        class="text-left flex flex-col rounded-xl dark:bg-primary-dark-800/50 bg-primary-800 ring-1 ring-gray-900/5 dark:text-white shadow w-full divide-gray-300 dark:divide-neutral-400"
    >
        <section
            class="bg-white dark:bg-primary-dark-800/70 dark:hover:bg-primary-dark-600 hover:bg-primary-800 p-3 rounded-xl ring-1 ring-gray-900/5 flex gap-4 w-full items-center flex-wrap"
        >
            <div class="flex flex-col gap-2 sm:gap-1 flex-1 relative">
                <div class="flex gap-x-4 gap-y-2 items-center">
                    <h2 class="truncate capitalize group" :title="data.name">
                        {{ data.name }}
                        <div
                            v-if="(data.description?.length ?? 0) > 0"
                            class="flex z-30 left-20 bottom-10 absolute opacity-0 group-hover:opacity-100 transition-opacity ease-in-out duration-500 md:max-w-72 text-sm p-3 bg-white dark:odd:bg-primary-dark-600/70 dark:bg-neutral-800/70 backdrop-blur-lg border dark:border-none rounded-md shadow-md border-neutral-200/70 gap-2 items-center"
                        >
                            <ProiconsCommentExclamation class="h-4 w-4" />
                            <p class="text-wrap">{{ data.description }}</p>
                        </div>
                    </h2>
                    <p
                        v-if="data.summary"
                        class="flex justify-end gap-1 flex-1 line-clamp-3 sm:line-clamp-1 text-wrap text-ellipsis sm:text-nowrap text-neutral-500 dark:text-neutral-400"
                    >
                        {{ data.summary }}
                    </p>
                </div>
                <div class="flex justify-between md:grid grid-cols-3 flex-1 w-full md:w-fit gap-4 md:gap-1 flex-wrap">
                    <div class="flex gap-1 h-fit">
                        <img
                            class="aspect-square h-4 my-auto rounded-t-xl xs:rounded-full object-cover"
                            :src="`https://ui-avatars.com/api/?name=${data.user[0]}&amp;color=7F9CF5&amp;background=random`"
                            alt="aminushki"
                        />
                        <h4 class="text-xs text-neutral-500 dark:text-neutral-400 truncate line-clamp-1 capitalize" title="">
                            {{ data.user }}
                        </h4>
                    </div>

                    <span class="flex gap-1 flex-1">
                        <h4 class="text-xs text-neutral-500 dark:text-neutral-400 truncate line-clamp-1 capitalize" title="Time">
                            {{
                                data.ended_at
                                    ? `Finished: ${toFormattedDate(new Date(data.ended_at + ' UTC'), true, within24Hrs(data.ended_at + ' UTC') ? { hour: '2-digit', minute: '2-digit' } : undefined)}`
                                    : data.started_at
                                      ? `Started: ${toFormattedDate(new Date(data.started_at + ' UTC'), true, within24Hrs(data.started_at + ' UTC') ? { hour: '2-digit', minute: '2-digit' } : undefined)}`
                                      : `Created: ${toFormattedDate(new Date(data.created_at), true, within24Hrs(data.created_at) ? { hour: '2-digit', minute: '2-digit' } : undefined)}`
                            }}
                        </h4>

                        <h4 class="text-xs text-neutral-500 dark:text-neutral-400 truncate line-clamp-1 capitalize" title="Time">
                            {{ toFormattedDuration(data.duration) }}
                        </h4>
                    </span>

                    <span class="flex gap-x-4">
                        <h4
                            class="text-xs text-neutral-500 dark:text-neutral-400 truncate line-clamp-1 capitalize w-20"
                            title="Pending Tasks"
                        >
                            Sub Tasks: {{ data.sub_tasks_total }}
                        </h4>
                        <h4
                            class="text-xs text-neutral-500 dark:text-neutral-400 truncate line-clamp-1 capitalize"
                            v-if="data.sub_tasks_failed"
                            title="Failed Tasks"
                        >
                            Failed: {{ data.sub_tasks_failed }}
                        </h4>
                        <h4
                            class="text-xs text-neutral-500 dark:text-neutral-400 truncate line-clamp-1 capitalize"
                            v-else
                            title="Pending Tasks"
                        >
                            Pending: {{ data.sub_tasks_pending }}
                        </h4>
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2 w-full md:w-fit">
                <PulseDoughnutChart
                    class="!h-8 !w-8 md:!hidden"
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
                                backgroundColor: ['#f59e0b', '#9333ea', '#be123c'],
                                hoverBackgroundColor: ['#f59e0b', '#9333ea', '#e11d48'],
                            },
                        ],
                    }"
                />
                <p class="w-full text-left md:!hidden text-xs">
                    {{ Math.ceil((data.sub_tasks_complete / (data.sub_tasks_total ? data.sub_tasks_total : 1)) * 100) }}%
                </p>
                <div class="px-2 text-xs hidden md:flex flex-col gap-1 h-fit min-w-32 flex-1">
                    <p class="w-full text-left pe-8">
                        {{ Math.ceil((data.sub_tasks_complete / (data.sub_tasks_total ? data.sub_tasks_total : 1)) * 100) }}% Processed
                    </p>
                    <div class="rounded-full h-1 w-full bg-primary-dark-900 flex overflow-clip">
                        <div
                            :class="`h-1 bg-purple-600 rounded-full`"
                            :style="`width: ${(data.sub_tasks_complete / (data.sub_tasks_total ? data.sub_tasks_total : 1)) * 100}%;`"
                        ></div>
                        <div
                            :class="`h-1 bg-amber-500 rounded-full`"
                            :style="`width: ${(data.sub_tasks_pending / (data.sub_tasks_total ? data.sub_tasks_total : 1)) * 100}%;`"
                        ></div>
                        <div
                            :class="`h-1 bg-rose-600 rounded-full`"
                            :style="`width: ${(data.sub_tasks_failed / (data.sub_tasks_total ? data.sub_tasks_total : 1)) * 100}%;`"
                        ></div>
                    </div>
                </div>
                <div class="flex gap-1 items-center ml-auto">
                    <ChipTag
                        :class="`h-6 shadow-sm`"
                        :colour="`!text-white ${
                            data.status === 'pending'
                                ? 'bg-[#e4e4e4] dark:bg-white !text-neutral-900'
                                : data.status === 'processing'
                                  ? 'bg-purple-600 dark:bg-purple-700'
                                  : data.status === 'completed'
                                    ? 'bg-[#660099] '
                                    : data.status === 'incomplete' || data.status === 'cancelled'
                                      ? 'bg-amber-500 !text-neutral-900 '
                                      : 'bg-rose-600 dark:bg-rose-700 '
                        }`"
                        :label="data.status"
                    />
                    <Popover
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
                                    <ButtonText
                                        v-else
                                        class="h-8 text-rose-600 dark:!bg-rose-700 disabled:opacity-60"
                                        :title="'Remove Record From Server'"
                                        @click.stop.prevent="$emit('clickAction')"
                                        disabled
                                    >
                                        <template #text> Remove </template>
                                        <template #icon> <ProiconsDelete class="h-4 w-4" /></template>
                                    </ButtonText>
                                </div>
                            </div>
                        </template>
                    </Popover>
                    <ButtonIcon
                        variant="ghost"
                        :class="`hover:shadow-md rounded-xl transition-transform duration-200 ease-in-out p-1 w-fit h-fit dark:hover:bg-neutral-900 hover:bg-gray-100 ${expanded ? 'rotate-180' : 'rotate-0'}`"
                        @click="expanded = !expanded"
                        :title="`${expanded ? 'Hide Sub Tasks' : 'Show Sub Tasks'}`"
                    >
                        <template #icon>
                            <ProiconsChevronDown :class="`w-6 h-6 hover:text-violet-600 dark:hover:text-violet-500`" />
                        </template>
                    </ButtonIcon>
                </div>
            </div>
            <!-- <section class="flex flex-col sm:flex-row sm:justify-between w-full text-sm text-neutral-500 dark:text-neutral-400">
                <h3 class="w-full text-wrap line-clamp-2" :title="`Description`">
                    {{ data.summary ?? 'This task has no summary.' }}
                </h3>
                <span>
                    <h3 class="truncate sm:text-right w-full line-clamp-1 capitalize" title="Time Started">
                        Started:
                        {{ data.created_at ? toFormattedDate(new Date(data.created_at)) : 'Unknown' }}
                    </h3>
                    <h3 class="truncate sm:text-right w-full line-clamp-1 capitalize" title="Time Started">Duration: 23m</h3>
                </span>
            </section> -->
        </section>

        <section
            :class="`flex flex-col gap-1 transition-all duration-300 ease-in-out ${expanded ? `py-1 h-fit` : 'overflow-hidden '}`"
            :style="`height: ${expanded ? `${Math.max(52, (data?.sub_tasks?.length ?? 0) * (68 + 4))}px` : '0px'};`"
        >
            <SubTaskCard v-for="subTask in data.sub_tasks" :key="subTask.id" :data="subTask" />
            <div
                v-if="(data.sub_tasks?.length ?? 0) == 0"
                class="col-span-full flex items-center justify-center text-center text-gray-500 dark:text-gray-400 uppercase tracking-wider w-full gap-2 text-sm my-auto"
            >
                {{ 'No Sub Tasks Found' }}
            </div>
        </section>
    </div>
</template>
