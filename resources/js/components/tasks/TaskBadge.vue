<script setup lang="ts">
import type { TaskStatus } from '@/types/types';

import { taskStatusClass } from '@/service/util/taskStatusStyle';
import { BadgeTag } from '@/components/cedar-ui/badge';
import { cn } from '@aminnausin/cedar-ui';

defineProps<{ status: TaskStatus }>();
</script>

<template>
    <BadgeTag :class="cn('flex h-6 items-center text-xs text-white', taskStatusClass(status), $attrs.class)" :label="status" />
</template>
