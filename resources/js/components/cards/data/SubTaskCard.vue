<script setup lang="ts">
import type { SubTaskResource } from '@/types/resources';

import { toFormattedDate, toFormattedDuration, toTimeSpan, within24Hrs } from '@/service/util';
import { ButtonIcon } from '@/components/cedar-ui/button';
import { HoverCard } from '@/components/cedar-ui/hover-card';

import TaskProgressBar from '@/components/tasks/TaskProgressBar.vue';
import TaskBadge from '@/components/tasks/TaskBadge.vue';

import ProiconsArrowReply from '~icons/proicons/arrow-reply';
import ProiconsDelete from '~icons/proicons/delete';

const props = defineProps<{ data: SubTaskResource; isScreenLarge?: boolean }>();
const emit = defineEmits(['clickAction']);
</script>

<template>
    <span :class="['flex w-full text-left', 'content-auto [contain-intrinsic-size:auto_60px]']">
        <div class="data-card hover:ring-primary-active flex flex-1 flex-wrap items-center gap-4 truncate rounded-xl p-3 shadow-xs ring-1 ring-gray-900/5 ring-inset">
            <div class="group text-foreground-1 flex min-w-16 flex-1 flex-col gap-1">
                <HoverCard :contentTitle="data.name" :content="data.summary?.trim()" class="flex items-center gap-x-4 gap-y-2 truncate">
                    <template #trigger>
                        <h2 class="group text-foreground-0 truncate capitalize">{{ data.id }} - {{ data.name }}</h2>
                        <p v-if="data.summary" class="hidden max-w-48 truncate md:block lg:max-w-20 xl:max-w-64">
                            {{ data.summary }}
                        </p>
                    </template>
                </HoverCard>
                <div class="flex gap-x-8 gap-y-2">
                    <h4
                        class="trunc"
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
                    <h4 class="sm:trunc hidden w-20 text-xs sm:block lg:w-fit" title="Duration">
                        {{ data.duration || data.ended_at ? 'Duration:' : data.started_at ? 'Started: ' : 'Scheduled: ' }}
                        {{
                            data.duration || data.ended_at
                                ? toFormattedDuration(data.duration, false)
                                : toTimeSpan(data.started_at ?? data.created_at, data.started_at ? ' UTC' : '')
                        }}
                    </h4>
                </div>
            </div>

            <TaskProgressBar class="hidden md:flex" :progress-pct="data.progress" :segments="[{ status: data.status, progressPct: data.progress }]" />

            <div class="flex shrink-0 items-center gap-2 sm:flex-none">
                <TaskBadge :status="data.status" />
                <ButtonIcon
                    :variant="'ghost'"
                    class="hover:dark:bg-surface-1 hover:bg-surface-6 text-foreground-1 hover:text-danger-2 hover:dark:text-danger-3 size-6 rounded-full p-0 transition-none"
                    title="Delete Sub Task Record"
                    :useDefaultStyle="false"
                    @click="$emit('clickAction', 'subTask')"
                >
                    <template #icon> <ProiconsDelete class="size-4" /></template>
                </ButtonIcon>
            </div>
        </div>
        <ProiconsArrowReply class="mx-2 my-auto hidden size-6 shrink-0 -scale-y-100 sm:block" />
    </span>
</template>
