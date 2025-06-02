<script setup lang="ts">
import { type SubTaskResource, type TaskResource } from '@/types/resources';

import { toFormattedDate, toFormattedDuration, toTimeSpan, within24Hrs } from '@/service/util';
import { computed, ref, useTemplateRef, watch } from 'vue';
import { getSubTasks } from '@/service/siteAPI';

import PulseDoughnutChart from '@/components/charts/PulseDoughnutChart.vue';
import ButtonCorner from '@/components/inputs/ButtonCorner.vue';
import BasePopover from '@/components/pinesUI/BasePopover.vue';
import SubTaskCard from '@/components/cards/SubTaskCard.vue';
import ButtonIcon from '@/components/inputs/ButtonIcon.vue';
import ButtonText from '@/components/inputs/ButtonText.vue';
import TableBase from '@/components/table/TableBase.vue';
import HoverCard from '@/components/cards/HoverCard.vue';
import ChipTag from '@/components/labels/ChipTag.vue';

import ProiconsMoreVertical from '~icons/proicons/more-vertical';
import ProiconsChevronDown from '~icons/proicons/chevron-down';
import ProiconsArrowSync from '~icons/proicons/arrow-sync';
import ProiconsDelete from '~icons/proicons/delete';

const props = defineProps<{ data: TaskResource; isScreenSmall?: boolean; isScreenLarge?: boolean }>();
const emit = defineEmits(['clickAction']);
const subTasks = ref<SubTaskResource[]>([]);
const popover = useTemplateRef('popover');
const subTasksFetched = ref(false);
const expanded = ref(false);
const progress = computed(() => {
    const roundDown = (val: number) => {
        return Math.floor(val * 100);
    };

    if (!props.data.id || !props.data.sub_tasks_total) return { complete: 0, processing: 0, failed: 0, pending: 100 };
    const complete = roundDown(props.data.sub_tasks_complete / props.data.sub_tasks_total);
    const failed = roundDown(props.data.sub_tasks_failed / props.data.sub_tasks_total);
    const pending = Math.ceil((Math.max(props.data.sub_tasks_total - props.data.sub_tasks_complete - props.data.sub_tasks_failed, 0) / props.data.sub_tasks_total) * 100);
    const processing = Math.max(100 - complete - failed - pending, 0);

    return {
        complete,
        processing,
        failed,
        pending,
    };
});

const toggleExpanded = async () => {
    if (!subTasksFetched.value && !expanded.value) await loadSubTasks();
    expanded.value = !expanded.value;
};

const loadSubTasks = async () => {
    const { data } = await getSubTasks(props.data.id);

    if (Array.isArray(data?.data)) subTasks.value = data.data;

    subTasksFetched.value = true;
};

const handleClick = (type: '' | 'cancel' | 'subTask' = '') => {
    popover.value?.handleClose();
    emit('clickAction', type);
};

watch(
    () => props.data,
    () => {
        if (!expanded.value) {
            subTasksFetched.value = false;
            return;
        }

        loadSubTasks();
    },
);
</script>
<template>
    <div
        class="text-left flex flex-col rounded-xl dark:bg-primary-dark-800/50 bg-primary-800 ring-1 ring-gray-900/5 dark:text-white shadow w-full divide-gray-300 dark:divide-neutral-400"
    >
        <section
            class="bg-white dark:bg-primary-dark-800/70 dark:hover:bg-primary-dark-600 hover:bg-primary-800 p-3 rounded-xl ring-1 ring-gray-900/5 flex gap-4 w-full items-center flex-wrap"
        >
            <div class="flex flex-col gap-2 sm:gap-1 flex-1 relative">
                <HoverCard :content="data.description ?? ''" :content-title="data.summary" class="flex gap-x-4 gap-y-2 items-center">
                    <template #trigger>
                        <h2 class="truncate capitalize group">{{ data.id }} - {{ data.name }}</h2>
                        <p v-if="data.summary" class="truncate text-neutral-600 dark:text-neutral-400 max-w-64 hidden md:block">
                            {{ data.summary }}
                        </p>
                    </template>
                </HoverCard>
                <div class="flex md:grid md:grid-cols-3 xl:grid-cols-5 flex-1 w-full gap-1 gap-x-4 flex-wrap">
                    <div class="flex gap-1 h-fit">
                        <img
                            class="aspect-square h-4 my-auto rounded-t-xl xs:rounded-full object-cover"
                            :src="`https://ui-avatars.com/api/?name=${data.user[0]}&amp;color=7F9CF5&amp;background=random`"
                            alt="username"
                        />
                        <h4 class="text-xs text-neutral-600 dark:text-neutral-400 truncate line-clamp-1 capitalize" title="">
                            {{ data.user }}
                        </h4>
                    </div>

                    <span class="flex gap-1 w-full flex-wrap md:flex-nowrap col-span-2">
                        <h4
                            class="text-xs text-neutral-600 dark:text-neutral-400 xs:truncate capitalize flex-auto xs:flex-1 md:flex-auto break-words"
                            :title="
                                `Created: ${data.created_at}\n` +
                                (data.started_at ? `Started: ${data.started_at} UTC\n` : '') +
                                (data.ended_at ? `Finished: ${data.ended_at} UTC\n` : '')
                            "
                        >
                            {{
                                data.ended_at
                                    ? `Finished: ${toFormattedDate(new Date(data.ended_at + ' UTC'), true, within24Hrs(data.ended_at + ' UTC') ? { hour: '2-digit', minute: '2-digit' } : undefined)}`
                                    : data.started_at
                                      ? `Started: ${toFormattedDate(new Date(data.started_at + ' UTC'), true, within24Hrs(data.started_at + ' UTC') ? { hour: '2-digit', minute: '2-digit' } : undefined)}`
                                      : `Created: ${toFormattedDate(new Date(data.created_at), true, within24Hrs(data.created_at) ? { hour: '2-digit', minute: '2-digit' } : undefined)}`
                            }}
                        </h4>
                        <h4
                            v-if="within24Hrs(data.started_at ?? data.created_at) || data.duration"
                            class="text-xs text-neutral-600 dark:text-neutral-400 truncate capitalize md:ml-auto"
                            title="Time"
                        >
                            {{ data.duration ? 'Duration:' : data.started_at ? 'Started: ' : 'Scheduled: ' }}
                        </h4>
                        <h4 class="text-xs text-neutral-600 dark:text-neutral-400 capitalize md:me-auto line-clamp-1" title="Time">
                            {{ data.duration ? toFormattedDuration(data.duration, false) : toTimeSpan(data.started_at ?? data.created_at, data.started_at ? ' UTC' : '') }}
                        </h4>
                    </span>

                    <span
                        :class="`grid xs:grid-cols-2 col-span-3 md:col-span-2 gap-x-4`"
                        :title="`Sub Tasks: ${data.sub_tasks_total}\n\nPending: ${data.sub_tasks_pending}\n\nCompleted: ${data.sub_tasks_complete}\n\nFailed: ${data.sub_tasks_failed}`"
                    >
                        <h4 class="text-xs text-neutral-600 dark:text-neutral-400 truncate line-clamp-1 capitalize w-24">Sub Tasks: {{ data.sub_tasks_total }}</h4>
                        <h4 class="text-xs text-neutral-600 dark:text-neutral-400 truncate line-clamp-1 capitalize" v-if="data.sub_tasks_failed">
                            Failed: {{ data.sub_tasks_failed }}
                        </h4>
                        <h4 class="text-xs text-neutral-600 dark:text-neutral-400 truncate line-clamp-1 capitalize" v-else>Pending: {{ data.sub_tasks_pending }}</h4>
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2 w-full md:w-fit">
                <PulseDoughnutChart
                    v-if="isScreenSmall ?? false"
                    v-cloak
                    class="!h-8 !w-8 sm:!hidden"
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
                        labels: ['Completed', 'Pending', 'Failed', 'Unknown'],
                        datasets: [
                            {
                                data: [
                                    progress.complete,
                                    progress.pending,
                                    progress.failed,
                                    Math.min(Math.max(100 - progress.complete - progress.pending - progress.failed, 0), 100),
                                ],
                                backgroundColor: ['#9333ea', '#f59e0b', '#be123c', '#9333ea26'],
                                hoverBackgroundColor: ['#9333ea', '#f59e0b', '#e11d48', '#9333ea26'],
                            },
                        ],
                    }"
                />
                <p class="w-full text-left sm:!hidden text-xs">
                    {{ Math.ceil((Math.max(data.sub_tasks_complete, 0) / (data.sub_tasks_total ? data.sub_tasks_total : 1)) * 100) }}%
                </p>
                <div class="px-2 text-xs hidden sm:flex flex-col gap-1 h-fit min-w-32 flex-1">
                    <p class="w-full text-left">{{ Math.ceil((Math.max(data.sub_tasks_complete, 0) / (data.sub_tasks_total ? data.sub_tasks_total : 1)) * 100) }}% Processed</p>
                    <div class="h-1 w-full bg-primary-dark-900 flex overflow-clip rounded-full">
                        <div
                            :class="`${data.sub_tasks_failed + data.sub_tasks_pending == 0 ? 'rounded-full' : 'rounded-l-full'} bg-purple-600`"
                            :style="`width: ${progress.complete}%;`"
                        ></div>
                        <div
                            :class="`${data.sub_tasks_complete === 0 ? 'rounded-l-full' : ''} ${data.sub_tasks_failed === 0 ? 'rounded-r-full' : ''} bg-amber-500`"
                            :style="`width: ${progress.pending}%;`"
                        ></div>
                        <div
                            :class="`${data.sub_tasks_complete + data.sub_tasks_pending == 0 ? 'rounded-full' : 'rounded-r-full'} bg-rose-600`"
                            :style="`width: ${progress.failed}%;`"
                        ></div>
                    </div>
                </div>
                <div class="flex gap-1 items-center ml-auto">
                    <span class="w-24 flex items-center justify-end">
                        <ChipTag
                            :class="`h-6 shadow-sm`"
                            :colour="`${data.status === 'pending' ? 'bg-[#e4e4e4] dark:bg-white !text-gray-900' : '!text-white'} ${
                                data.status === 'processing'
                                    ? 'bg-purple-600 dark:bg-purple-700'
                                    : data.status === 'completed'
                                      ? 'bg-[#660099] '
                                      : data.status === 'incomplete' || data.status === 'cancelled'
                                        ? 'bg-amber-500 !text-gray-900 '
                                        : 'bg-rose-600 dark:bg-rose-700 '
                            }`"
                            :label="data.status"
                        />
                    </span>
                    <BasePopover
                        ref="popover"
                        popoverClass="!w-40 rounded-lg mt-8"
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
                                    <ButtonText class="h-8 dark:!bg-neutral-950" :title="'Run Again'" text="Run Again" disabled>
                                        <template #icon> <ProiconsArrowSync class="h-4 w-4" /></template>
                                    </ButtonText>
                                    <ButtonText
                                        v-if="data.status_key >= 0 && data.status_key <= 1"
                                        class="h-8 text-rose-600 dark:!bg-rose-700 disabled:opacity-60"
                                        text="Cancel Task"
                                        @click.stop.prevent="handleClick('cancel')"
                                        :title="'Cancel Task'"
                                    >
                                        <template #icon> <ProiconsDelete class="h-4 w-4" /></template>
                                    </ButtonText>
                                    <ButtonText
                                        v-else
                                        class="h-8 text-rose-600 dark:!bg-rose-700 disabled:opacity-60"
                                        @click.stop.prevent="handleClick()"
                                        text="Remove"
                                        :title="'Remove Task\'s Record From Server'"
                                    >
                                        <template #icon> <ProiconsDelete class="h-4 w-4" /></template>
                                    </ButtonText>
                                </div>
                            </div>
                        </template>
                    </BasePopover>
                    <ButtonIcon
                        variant="ghost"
                        :class="`hover:shadow-md rounded-xl transition-transform duration-200 ease-in-out p-1 w-fit h-fit dark:hover:bg-neutral-900 hover:bg-gray-100 ${expanded ? 'rotate-180' : 'rotate-0'}`"
                        @click="toggleExpanded"
                        :title="`${expanded ? 'Hide Sub Tasks' : 'Show Sub Tasks'}`"
                    >
                        <template #icon>
                            <ProiconsChevronDown :class="`w-6 h-6 hover:text-violet-600 dark:hover:text-violet-500`" />
                        </template>
                    </ButtonIcon>
                </div>
            </div>
        </section>

        <section
            :class="`scrollbar-hide flex flex-col gap-1 transition-all duration-300 ease-in-out rounded-xl px-1  ${expanded ? `py-1 max-h-[800px] overflow-y-auto` : 'overflow-hidden max-h-0'}`"
        >
            <div
                v-if="(subTasks.length ?? 0) == 0"
                class="col-span-full flex items-center justify-center text-center text-gray-500 dark:text-gray-400 uppercase tracking-wider w-full gap-2 text-sm my-2"
            >
                {{ 'No Sub Tasks Found' }}
            </div>
            <SubTaskCard
                v-if="subTasks.length <= 8"
                v-for="subTask in subTasks.slice(0, Math.min(subTasks.length, 8))"
                :key="subTask.id"
                :data="subTask"
                :isScreenSmall="isScreenLarge"
                @click-action="$emit('clickAction', 'subTask', subTask.id)"
            />
            <TableBase
                v-else
                :class="'p-1'"
                :pagination-class="'sm:pe-10 -mt-2 text-sm'"
                :use-pagination="true"
                :data="subTasks"
                :row="SubTaskCard"
                :row-attributes="{ isScreenLarge }"
                :loading="false"
                :table-styles="'gap-4 xs:gap-2'"
                :use-toolbar="false"
                :items-per-page="8"
                :clickAction="
                    (id: number) => {
                        $emit('clickAction', 'subTask', id);
                    }
                "
            />
        </section>
    </div>
</template>
