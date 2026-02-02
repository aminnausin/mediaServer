<script setup lang="ts">
import type { SubTaskResource } from '@/types/resources';

import { toFormattedDate, toFormattedDuration, toTimeSpan, within24Hrs } from '@/service/util';
import { ButtonIcon } from '@/components/cedar-ui/button';
import { HoverCard } from '@/components/cedar-ui/hover-card';
import { BadgeTag } from '@/components/cedar-ui/badge';
import { cn } from '@aminnausin/cedar-ui';

import ProiconsArrowReply from '~icons/proicons/arrow-reply';
import ProiconsDelete from '~icons/proicons/delete';

const props = defineProps<{ data: SubTaskResource; isScreenLarge?: boolean }>();
const emit = defineEmits(['clickAction']);
</script>

<template>
    <span class="flex w-full rounded-xl text-left">
        <section class="data-card hover:ring-primary-active flex flex-1 flex-wrap items-center gap-4 truncate rounded-md p-3 shadow-xs ring-1 ring-gray-900/5 ring-inset">
            <div class="group text-foreground-1 relative flex flex-1 flex-col gap-1 truncate">
                <HoverCard
                    :content="data.summary ? `${data.name ? `${data.name}\n` : ''}` + data.summary.trim() : (data.name ?? '')"
                    class="flex items-center gap-x-4 gap-y-2 truncate"
                >
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

            <div class="hidden h-fit min-w-32 flex-col gap-1 px-2 text-xs lg:flex">
                <p class="w-full pe-8 text-left">{{ data.progress }}% Processed</p>
                <div class="bg-primary-dark-900 flex h-1 w-full overflow-clip rounded-full">
                    <span
                        :class="['h-1 rounded-full', { 'bg-primary': data.status === 'completed' }, data.status === 'failed' ? 'bg-danger-2' : 'bg-amber-500 dark:bg-amber-600']"
                        :style="`width: ${data.progress}%;`"
                    ></span>
                </div>
            </div>

            <div class="flex shrink-0 items-center gap-2 sm:flex-none">
                <BadgeTag
                    :class="
                        cn(
                            'flex h-6 items-center',
                            data.status === 'pending' ? 'bg-[#e4e4e4] text-gray-900 dark:bg-white' : 'text-white',
                            { 'bg-primary dark:bg-primary-dark': data.status === 'processing' },
                            { 'bg-amber-500 dark:bg-amber-600': data.status === 'incomplete' },
                            { 'bg-danger-2 dark:bg-danger-3': data.status === 'cancelled' || data.status === 'failed' },
                            { 'bg-[#660099]': data.status === 'completed' },
                        )
                    "
                    :label="data.status"
                />
                <ButtonIcon
                    :variant="'ghost'"
                    class="hover:dark:bg-surface-1 hover:bg-surface-6 text-foreground-1 hover:text-danger-2 hover:dark:text-danger-3 size-6 rounded-full p-0 transition-none"
                    label="Remove Sub Task Record From Server"
                    :useDefaultStyle="false"
                    @click="$emit('clickAction', 'subTask')"
                >
                    <template #icon> <ProiconsDelete class="size-4" /></template>
                </ButtonIcon>
            </div>
        </section>
        <ProiconsArrowReply class="mx-2 my-auto hidden size-6 shrink-0 -scale-y-100 sm:block" />
    </span>
</template>

<style lang="css" scoped>
@reference '../../../../css/app.css';

.trunc {
    @apply line-clamp-1 truncate capitalize;
}
</style>
