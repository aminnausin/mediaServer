<script setup lang="ts">
import { ButtonBase } from '.';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        type?: 'reset' | 'submit' | 'button' | undefined;
        disabled?: boolean;
        title?: string;
        variant?: 'default' | 'ghost' | 'transparent';
        to?: string;
        href?: string;
        target?: string;
    }>(),
    {
        variant: 'default',
        target: '_self',
        type: 'button',
    },
);

const wrapperProps = computed(() => {
    let wProps = {};

    if (props.to || props.href) wProps = { title: props.title ?? 'Link' };

    return {
        title: props.title ?? 'Button',
        to: props.to,
        href: props.href,
        target: props.target ?? '_blank',
        type: props.type,
        ...wProps,
    };
});
</script>

<template>
    <ButtonBase
        v-bind="wrapperProps"
        :class="[
            'button-base aspect-square',
            {
                'hover:ring-surface-1 focus:ring-surface-1 hover:bg-transparent hover:ring-1 focus:bg-transparent focus:ring-1 focus:outline-hidden': variant === 'transparent',
                'ring-r-button hover:ring-primary-muted focus:ring-primary hocus:ring-2 bg-surface-2 shadow-xs ring-1 focus:outline-hidden': variant === 'default',
            },
        ]"
        :aria-label="$slots.text ? undefined : title"
        :disabled="disabled"
    >
        <!-- Should remove (only have icon but that should be the default not named) -->
        <slot name="text"> </slot>
        <slot name="icon"> </slot>
        <slot></slot>
    </ButtonBase>
</template>
