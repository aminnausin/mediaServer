<script setup lang="ts">
import type { ButtonType, FormButtonVariant } from '@aminnausin/cedar-ui';

import { ButtonBase } from '.';
import { computed } from 'vue';
import { cn } from '@aminnausin/cedar-ui';

const props = withDefaults(
    defineProps<{
        disabled?: boolean;
        type?: ButtonType;
        variant?: FormButtonVariant;
        label?: string;
    }>(),
    {
        disabled: false,
        type: 'button',
    },
);
const variantClass = computed(() => {
    switch (props.variant) {
        case 'submit':
            return [
                'text-foreground-i font-medium border-transparent',
                'focus:ring-primary bg-surface-i hocus:bg-surface-i/90 dark:hover:bg-foreground-4',
            ];
        case 'reset':
            return ['font-medium', 'hocus:ring-foreground-4-hover', 'hocus:bg-surface-3'];
        case 'auth': // This one is styled from Laravel
            return [
                'bg-gray-800 dark:bg-gray-200 hover:dark:bg-gray-300',
                'border-transparent font-semibold text-xs uppercase tracking-widest',
                'text-foreground-i hover:bg-gray-700 dark:hover:foreground-0',
                'focus:bg-gray-700 dark:focus:bg-foreground-0 active:bg-gray-900 dark:active:bg-gray-300',
                'focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800',
            ];
        default:
            return ['focus:ring-r-button dark:focus:ring-r-disabled hocus:bg-surface-3'];
    }
});
</script>
<template>
    <ButtonBase
        :type="type"
        :class="
            cn('px-4', 'border-r-button ring-offset-surface-0 border', 'inline-flex', 'focus:ring-1 focus:ring-offset-1', ...variantClass)
        "
        :disabled="disabled"
        :aria-label="label"
    >
        <slot name="text"></slot>
        <slot></slot>
    </ButtonBase>
</template>
