<script setup lang="ts">
import { cn } from '@aminnausin/cedar-ui';

defineProps<{ controlId: string }>();
defineOptions({ inheritAttrs: false });
</script>
<template>
    <label
        :class="
            cn(
                'text-foreground-0 bg-primary-800 flex h-10 w-full items-center justify-between rounded-l-md p-3 py-2 text-left text-sm whitespace-nowrap capitalize shadow-xs ring-1 ring-neutral-200 ring-inset dark:bg-neutral-900 dark:ring-neutral-700',
                $attrs.class,
            )
        "
        :for="controlId"
    >
        <slot></slot>
    </label>
</template>
