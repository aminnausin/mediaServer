<script setup lang="ts">
import type { ContextMenuItem } from '@aminnausin/cedar-ui';

import { ButtonBase } from '@/components/cedar-ui/button';
import { computed } from 'vue';
import { cn } from '@aminnausin/cedar-ui';

const props = withDefaults(defineProps<ContextMenuItem & { divider?: boolean }>(), {
    selectedStyle: 'text-primary font-bold',
});

const wrapperProps = computed(() => {
    if (!props.url) return {};
    if (props.external) return { href: props.url, target: props.target ?? '_blank' };
    return { to: props.url, target: props.target };
});
</script>
<template>
    <div v-if="divider" class="bg-hr dark:bg-hr/30 -mx-1 my-1 h-px" />

    <ButtonBase
        v-else
        v-bind="wrapperProps"
        :class="cn({ [selectedStyle]: selected }, 'hover:bg-overlay-accent h-7 w-full justify-start rounded-sm px-2 py-1.5 select-none', style)"
        :disabled="disabled"
        :onclick="
            () => {
                if (action) action();
            }
        "
    >
        <slot name="icon">
            <component v-if="icon" :is="icon" class="size-4 shrink-0" />
            <span v-else class="size-4 shrink-0"> </span>
        </slot>
        <span class="mr-auto truncate text-nowrap">{{ text }}</span>
        <span class="tracking-widest opacity-60" v-if="shortcut">{{ shortcut }}</span>
    </ButtonBase>
</template>
