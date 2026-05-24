<script setup lang="ts">
import type { TaskStatus } from '@/types/types';

import { taskProgressBarClass } from '@/service/util/taskStatusStyle';
import { computed } from 'vue';
import { cn } from '@aminnausin/cedar-ui';

const props = withDefaults(
    defineProps<{
        progressPct: number;
        segments?: { progressPct: number; status: TaskStatus }[];
    }>(),
    {
        progressPct: 0,
        segments: () => [],
    },
);

const filteredSegments = computed(() => props.segments.filter((s) => s.progressPct));
</script>

<template>
    <div :class="cn('h-fit min-w-32 flex-col gap-1 px-2', $attrs.class)">
        <p class="w-full truncate text-left">
            <span class="tabular-nums">{{ progressPct }}%</span> Processed
        </p>
        <div class="bg-primary-dark-900 flex h-1 w-full overflow-clip rounded-full" :title="filteredSegments.reduce((acc, s) => acc + `\n${s.status}: ${s.progressPct}%`, '')">
            <div
                v-for="(segment, index) in filteredSegments"
                :key="index"
                :class="cn(taskProgressBarClass(segment.status), { 'rounded-r-full': index === filteredSegments.length - 1 })"
                :style="`width: ${segment.progressPct}%;`"
            ></div>
        </div>
    </div>
</template>
