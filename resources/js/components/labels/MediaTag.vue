<script setup lang="ts">
import { BadgeTag } from '@/components/cedar-ui/badge';
import { cn } from '@aminnausin/cedar-ui';
const props = defineProps<{ class?: string }>();
</script>
<template>
    <BadgeTag
        :class="
            cn(
                'dark:hover:bg-primary/90 dark:hover:text-foreground-0 dark:bg-neutral-900',
                'hover:text-foreground-i text-foreground-7 hover:bg-primary bg-neutral-200',
                'max-h-[22px] leading-none shadow-sm select-none',
                props.class,
            )
        "
        v-bind="$attrs"
    >
        <slot />
    </BadgeTag>
</template>
