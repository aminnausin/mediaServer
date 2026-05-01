<script setup lang="ts">
import { RouterLink } from 'vue-router';
import { computed } from 'vue';
import { cn } from '@aminnausin/cedar-ui';

const props = withDefaults(
    defineProps<{
        title?: string;
        to?: string;
        targetElement?: HTMLElement;
        controls?: boolean;
        verticalOffset?: string;
        isActive?: boolean;
        class?: string | any[];
    }>(),
    {},
);

const wrapper = computed(() => {
    return props.to ? RouterLink : 'button';
});

const wrapperProps = computed(() => {
    const semanticTitle = props.to ? 'Link' : 'Button';
    return {
        'aria-label': props.title ?? `Player ${semanticTitle}`,
        title: props.title ?? `Player ${semanticTitle}`,
        to: props.to,
    };
});
</script>
<template>
    <component
        ref="el"
        :is="wrapper"
        :class="
            cn(
                'flex cursor-pointer items-center gap-1',
                'h-5 rounded-full px-2 py-0.5',
                'bg-neutral-900/30 text-white/80 hover:bg-neutral-900/50 hover:text-yellow-500',
                'border border-neutral-700/10',
                'duration-input transition-colors ease-in',
                { 'bg-neutral-800/90! text-yellow-500 hover:text-yellow-600': isActive },
                props.class,
            )
        "
        v-bind="wrapperProps"
    >
        <slot></slot>
    </component>
</template>
