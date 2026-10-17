<script setup lang="ts">
import type { TaskStatus } from '@/types/types';

import { taskStatusClass } from '@/service/util/taskStatusStyle';
import { BadgeTag } from '@/components/cedar-ui/badge';
import { computed } from 'vue';
import { cn } from '@aminnausin/cedar-ui';

import SvgSpinners90RingWithBg from '@/components/cedar-ui/icons/SvgSpinners90RingWithBg.vue';
import ProIconsCheckmark from '@/components/icons/ProIconsCheckmark.vue';
import ProiconsCalendar from '~icons/proicons/calendar';

const props = defineProps<{ status: TaskStatus }>();

const icon = computed(() => {
    switch (props.status) {
        case 'processing':
            return SvgSpinners90RingWithBg;
        case 'pending':
            return ProiconsCalendar;
        case 'completed':
            return ProIconsCheckmark;
        default:
            return null;
    }
});
</script>

<template>
    <BadgeTag :class="cn('flex h-6 items-center text-xs text-white', taskStatusClass(status), $attrs.class)" :label="status">
        <component v-if="icon" :is="icon" class="me-1 size-3" /> {{ status }}
    </BadgeTag>
</template>
