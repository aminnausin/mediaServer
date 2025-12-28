<script setup lang="ts">
import type { ButtonComponent, ButtonType } from '@aminnausin/cedar-ui';
import type { ComponentPublicInstance } from 'vue';

import { computed, useTemplateRef } from 'vue';
import { RouterLink } from 'vue-router';
import { cn } from '@aminnausin/cedar-ui';

const el = useTemplateRef<HTMLElement | ComponentPublicInstance | null>('el');

const props = withDefaults(
    defineProps<{
        disabled?: boolean;
        as?: ButtonComponent;
        to?: string;
        href?: string;
        ariaLabel?: string;
        class?: any;
        useSize?: boolean;
        target?: string;
        type?: ButtonType;
    }>(),
    {
        useSize: true,
        disabled: false,
        type: 'button',
    },
);

const wrapper = computed(() => props.as ?? (props.to ? RouterLink : props.href ? 'a' : 'button')); // Component is set here but props are set 1 level above
const wrapperProps = computed(() => (props.to ? { to: props.to } : { href: props.href }));

defineExpose({
    $el: () => {
        const value = el.value;

        if (!value) return null;

        if (value instanceof HTMLElement) {
            return value;
        }

        return value.$el as HTMLElement | null;
    },
});
</script>
<template>
    <component
        ref="el"
        :is="wrapper"
        :class="
            cn(
                'transition-input ease-in-out focus:outline-hidden', // Animation
                { 'button-disabled-pointer': wrapper === 'button' && disabled }, // Disabled Button
                { 'button-disabled': disabled }, // Disabled
                'flex items-center justify-center gap-2', // Layout
                'cursor-pointer', // Style
                { 'h-8 max-h-full p-2': useSize }, // Size
                'rounded-md', // Shape
                props.class,
            )
        "
        :target="wrapper !== 'button' ? target : undefined"
        :disabled="wrapper === 'button' ? disabled : undefined"
        :aria-disabled="wrapper !== 'button' ? disabled : undefined"
        :data-disabled="disabled"
        :aria-label="ariaLabel"
        :type="type"
        v-bind="wrapperProps"
    >
        <slot />
    </component>
</template>
