<script setup lang="ts">
import type { InputShellProps } from '@aminnausin/cedar-ui';

import { cn } from '@aminnausin/cedar-ui';

const props = withDefaults(defineProps<InputShellProps>(), { clampText: true });
</script>

<template>
    <slot
        name="input"
        :class="
            cn(
                'h-(--input-height)',
                'scrollable-input scrollbar-thin',
                'transition-input ease-in-out focus:outline-hidden',
                'bg-surface-2 placeholder:text-foreground-3 dark:placeholder:text-foreground-2 dark:bg-neutral-700',
                'hocus:ring-2 hover:ring-primary-muted focus-visible:ring-primary',
                { 'line-clamp-1 text-ellipsis whitespace-nowrap': clampText },
                'disabled:button-disabled',
                'block w-full border-none',
                'ring-r-input ring-1', // opinionated border
                'rounded-md text-sm shadow-xs', // opinionated input style
                props.class,
            )
        "
        v-bind="$attrs"
    />
</template>
