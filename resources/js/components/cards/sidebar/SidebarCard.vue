<script setup lang="ts">
import { computed, useSlots } from 'vue';
import { RouterLink } from 'vue-router';
import { cn } from '@aminnausin/cedar-ui';

const props = defineProps<{
    to?: string;
    href?: string;
    class?: string;
    target?: string;
    disabled?: boolean;
}>();

const wrapper = computed(() => (props.to ? RouterLink : 'a')); // Component is set here but props are set 1 level above

const wrapperProps = computed(() => {
    if (props.to) return { to: props.to };
    if (props.href) return { href: props.href };
    return {};
});

const slots = useSlots();
const isCompleteElement = computed(() => !!slots.header && !!slots.body);
</script>

<template>
    <component
        ref="el"
        :is="wrapper"
        :class="
            cn(
                'relative flex flex-col flex-wrap sm:flex-row',
                'group w-full cursor-pointer rounded-lg p-3 shadow-sm',
                'data-card',
                'transition-input hover:ring-primary-muted hover:dark:ring-primary/60 ring-1 ring-transparent ease-in-out ring-inset hover:ring-2',
                { 'gap-4 lg:gap-2': isCompleteElement },
                props.class,
            )
        "
        :target="target"
        :aria-disabled="disabled ?? false"
        v-bind="wrapperProps"
    >
        <slot>
            <section class="flex w-full items-center justify-between gap-4">
                <slot name="header"> </slot>
            </section>
            <section class="text-foreground-1 flex w-full flex-col flex-wrap gap-2 text-sm sm:flex-row sm:justify-between">
                <slot name="body"> </slot>
            </section>
        </slot>
    </component>
</template>
