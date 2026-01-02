<script setup lang="ts">
import type { ContextMenuItem } from '@aminnausin/cedar-ui';

import { ButtonBase } from '../button';
import { computed } from 'vue';
import { cn } from '@aminnausin/cedar-ui';

const props = withDefaults(defineProps<ContextMenuItem>(), {
    selectedStyle: 'text-primary font-bold',
});

const wrapperProps = computed(() => {
    if (!props.url) return {};
    if (props.external) return { href: props.url, target: props.target ?? '_blank' };
    return { to: props.url, target: props.target };
});
</script>
<template>
    <ButtonBase
        :class="cn({ selectedStyle: selected }, 'hover:bg-overlay-accent relative h-7 w-full rounded-sm px-2 py-1.5 text-xs select-none', style)"
        :disabled="disabled"
        :onclick="
            () => {
                if (action) action();
            }
        "
        v-bind="wrapperProps"
    >
        <slot name="icon">
            <component v-if="icon" :is="icon" class="size-4" />
            <span
                v-else
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="size-4"
            >
            </span>
        </slot>
        <span class="text-nowrap">{{ text }}</span>
        <span class="ml-auto text-xs tracking-widest opacity-60">{{ shortcut ?? '' }}</span>
    </ButtonBase>
</template>
