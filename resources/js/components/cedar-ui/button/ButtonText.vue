<script setup lang="ts">
import type { ButtonType, TextButtonVariant } from '@aminnausin/cedar-ui';

import { ButtonBase } from '.';
import { computed } from 'vue';
import { cn } from '@aminnausin/cedar-ui';

const props = withDefaults(
    defineProps<{
        type?: ButtonType;
        disabled?: boolean;
        title?: string;
        variant?: TextButtonVariant;
        to?: string;
        href?: string;
        target?: string;
        text?: string;
    }>(),
    {
        title: '',
        type: 'button',
        variant: 'default',
        target: '_blank',
    },
);

const wrapperProps = computed(() => {
    let wProps = {};

    if (props.to) wProps = { to: props.to, title: props.title ?? 'Link' };
    else if (props.href) wProps = { href: props.href, title: props.title ?? 'External Link' };

    return { title: props.title ?? 'Button', target: props.target ?? '_blank', type: props.type, ...wProps };
});

const variantClass = computed(() => {
    switch (props.variant) {
        case 'ghost':
            return [];
        case 'transparent':
            return ['hocus:ring-1 hocus:ring-surface-1 hocus:bg-transparent'];
        case 'form':
            return ['inline-flex px-4', 'border border-r-button', 'focus:ring-primary focus:ring-1 focus:ring-offset-1'];
        default:
            return ['shadow-xs', 'ring-1 ring-r-button hover:ring-primary-active focus:ring-primary hocus:ring-2', 'bg-surface-2'];
    }
});
</script>

<template>
    <button-base :class="[cn('ring-offset-surface-0 hocus:bg-surface-3', ...variantClass)]" v-bind="wrapperProps">
        <slot>
            <p class="line-clamp-1 flex-1 text-left" v-if="text">{{ text }}</p>
        </slot>
        <slot name="icon"> </slot>
    </button-base>
</template>
