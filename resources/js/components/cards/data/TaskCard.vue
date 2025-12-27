<script setup lang="ts">
import type { SubTaskResource, TaskResource } from '@/types/resources';

import { toFormattedDate, toFormattedDuration, toTimeSpan, within24Hrs } from '@/service/util';
import { computed, ref, useTemplateRef, watch } from 'vue';
import { ButtonIcon, ButtonText } from '@/components/cedar-ui/button';
import { getSubTasks } from '@/service/siteAPI';
import { TableBase } from '@/components/cedar-ui/table';
import { BadgeTag } from '@/components/cedar-ui/badge';
import { cn } from '@aminnausin/cedar-ui';

import PulseDoughnutChart from '@/components/charts/PulseDoughnutChart.vue';
import BasePopover from '@/components/pinesUI/BasePopover.vue';
import SubTaskCard from '@/components/cards/data/SubTaskCard.vue';
import HoverCard from '@/components/cards/HoverCard.vue';

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
    <div class="dark:bg-primary-dark-800/50 bg-primary-800 flex w-full flex-col rounded-xl text-left text-xs shadow-sm ring-1 ring-gray-900/5">
        <section class="data-card flex w-full flex-wrap items-center gap-4 rounded-xl p-3 ring-1 ring-gray-900/5">
            <div class="text-foreground-1 relative flex flex-1 flex-col gap-2 sm:gap-1">
                <HoverCard :content="data.description ?? ''" :content-title="data.summary" class="flex items-center gap-x-4 gap-y-2">
                    <template #trigger>
                        <h2 class="group text-foreground-0 truncate text-base capitalize">{{ data.id }} - {{ data.name }}</h2>
                        <p v-if="data.summary" class="hidden max-w-64 truncate md:block">
                            {{ data.summary }}
                        </p>
                    </template>
                </HoverCard>
                <div class="flex w-full flex-1 flex-wrap gap-1 gap-x-4 md:grid md:grid-cols-3 xl:grid-cols-5">
                    <div class="flex h-fit gap-1">
                        <img
                            class="xs:rounded-full my-auto aspect-square h-4 rounded-t-xl object-cover"
                            :src="`https://ui-avatars.com/api/?name=${data.user[0]}&amp;color=7F9CF5&amp;background=random`"
                            alt="username"
                        />
                        <h4>
                            {{ data.user }}
                        </h4>
                    </div>

                    <span class="col-span-2 flex w-full flex-wrap gap-1 md:flex-nowrap">
                        <h4
                            class="xs:truncate xs:flex-1 flex-auto wrap-break-word md:flex-auto"
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
                        <h4 v-if="within24Hrs(data.started_at ?? data.created_at) || data.duration" class="md:ml-auto" title="Time">
                            {{ data.duration ? 'Duration:' : data.started_at ? 'Started: ' : 'Scheduled: ' }}
                        </h4>
                        <h4 class="md:me-auto" title="Time">
                            {{ data.duration ? toFormattedDuration(data.duration, false) : toTimeSpan(data.started_at ?? data.created_at, data.started_at ? ' UTC' : '') }}
                        </h4>
                    </span>

                    <span
                        :class="`xs:grid-cols-2 col-span-3 grid gap-x-4 md:col-span-2`"
                        :title="`Sub Tasks: ${data.sub_tasks_total}\n\nPending: ${data.sub_tasks_pending}\n\nCompleted: ${data.sub_tasks_complete}\n\nFailed: ${data.sub_tasks_failed}`"
                    >
                        <h4 class="w-24">Sub Tasks: {{ data.sub_tasks_total }}</h4>
                        <h4 v-if="data.sub_tasks_failed">Failed: {{ data.sub_tasks_failed }}</h4>
                        <h4 v-else>Pending: {{ data.sub_tasks_pending }}</h4>
                    </span>
                </div>
            </div>

            <div class="flex w-full items-center gap-2 md:w-fit">
                <PulseDoughnutChart
                    v-if="isScreenSmall ?? false"
                    v-cloak
                    class="size-6!"
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
                <p class="w-full text-left sm:hidden">{{ Math.ceil((Math.max(data.sub_tasks_complete, 0) / (data.sub_tasks_total ? data.sub_tasks_total : 1)) * 100) }}%</p>
                <div class="hidden h-fit min-w-32 flex-1 flex-col gap-1 px-2 sm:flex">
                    <p class="w-full text-left">{{ Math.ceil((Math.max(data.sub_tasks_complete, 0) / (data.sub_tasks_total ? data.sub_tasks_total : 1)) * 100) }}% Processed</p>
                    <div class="bg-primary-dark-900 flex h-1 w-full overflow-clip rounded-full">
                        <div
                            :class="[data.sub_tasks_failed + data.sub_tasks_pending == 0 ? 'rounded-full' : 'rounded-l-full', 'bg-primary']"
                            :style="`width: ${progress.complete}%;`"
                        ></div>
                        <div
                            :class="[{ 'rounded-l-full': data.sub_tasks_complete === 0 }, { 'rounded-r-full': data.sub_tasks_failed === 0 }, 'bg-amber-500']"
                            :style="`width: ${progress.pending}%;`"
                        ></div>
                        <div
                            :class="[data.sub_tasks_complete + data.sub_tasks_pending == 0 ? 'rounded-full' : 'rounded-r-full', 'bg-danger-2']"
                            :style="`width: ${progress.failed}%;`"
                        ></div>
                    </div>
                </div>
                <div class="ml-auto flex items-center gap-1">
                    <span class="mr-1 flex w-24 items-center justify-end">
                        <BadgeTag
                            :class="
                                cn(
                                    'flex h-6 items-center',
                                    data.status === 'pending' ? 'bg-[#e4e4e4] text-gray-900 dark:bg-white' : 'text-white',
                                    { 'bg-primary dark:bg-primary-dark': data.status === 'processing' },
                                    { 'bg-amber-500 text-gray-900!': data.status === 'incomplete' },
                                    { 'bg-danger-2 dark:bg-rose-700': data.status === 'cancelled' },
                                    { 'bg-[#660099]': data.status === 'completed' },
                                )
                            "
                            :label="data.status"
                        />
                    </span>
                    <BasePopover
                        ref="popover"
                        popoverClass="w-40! rounded-lg mt-8"
                        :buttonComponent="ButtonIcon"
                        :button-attributes="{
                            class: 'hocus:dark:bg-surface-1 hocus:bg-surface-6 hover:text-primary dark:hover:text-primary-muted size-6 rounded-xl p-1',
                            variant: 'ghost',
                            label: 'Manage Permissions',
                        }"
                        @click.stop="() => {}"
                    >
                        <template #buttonIcon>
                            <ProiconsMoreVertical class="size-5" />
                        </template>
                        <template #content>
                            <div class="grid gap-4">
                                <div class="space-y-2">
                                    <h4 class="leading-none font-medium">Manage Task</h4>
                                </div>

                                <div class="grid gap-2">
                                    <ButtonText variant="form" title="Run Again" class="justify-between px-2 dark:bg-neutral-950" disabled>
                                        Run Again
                                        <template #icon> <ProiconsArrowSync class="size-4" /></template>
                                    </ButtonText>

                                    <ButtonText
                                        class="text-danger dark:text-foreground-0 dark:bg-danger-3 dark:hocus:bg-danger justify-between px-2"
                                        variant="form"
                                        :title="data.status_key >= 0 && data.status_key <= 1 ? 'Cancel Task' : 'Remove Task\'s Record From Server'"
                                        @click.stop.prevent="handleClick(data.status_key >= 0 && data.status_key <= 1 ? 'cancel' : undefined)"
                                    >
                                        {{ data.status_key >= 0 && data.status_key <= 1 ? 'Cancel Task' : 'Remove' }}
                                        <template #icon> <ProiconsDelete class="size-4" /></template>
                                    </ButtonText>
                                </div>
                            </div>
                        </template>
                    </BasePopover>
                    <ButtonIcon
                        variant="ghost"
                        :class="[
                            'hocus:dark:bg-surface-1 hocus:bg-surface-6 hover:text-primary dark:hover:text-primary-muted size-6 rounded-xl p-1',
                            expanded ? 'rotate-180' : 'rotate-0',
                        ]"
                        @click="toggleExpanded"
                        :title="`${expanded ? 'Hide Sub Tasks' : 'Show Sub Tasks'}`"
                    >
                        <template #icon>
                            <ProiconsChevronDown :class="`size-6`" />
                        </template>
                    </ButtonIcon>
                </div>
            </div>
        </section>

        <section
            :class="`scrollbar-hide flex flex-col gap-1 rounded-xl px-1 transition-all duration-300 ease-in-out ${expanded ? `max-h-[800px] overflow-y-auto py-1` : 'max-h-0 overflow-hidden'}`"
        >
            <TableBase
                :class="'p-1'"
                :pagination-class="'sm:pe-10 -mt-2 text-sm'"
                :use-pagination="subTasks.length > 8"
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
                no-results-message="No Sub Tasks Found"
            />
        </section>
    </div>
</template>

<style lang="css" scoped>
@reference '../../../../css/app.css';

h4 {
    @apply line-clamp-1 truncate capitalize;
}
</style>
