<script setup lang="ts">
import { ButtonBase } from '../button';

withDefaults(
    defineProps<{
        value: number | string;
        isSelected?: boolean;
        isDefault?: boolean;
        classOverride?: string;
    }>(),
    {
        isSelected: false,
    },
);
</script>

<template>
    <ButtonBase
        :class="[
            'hocus:bg-overlay-accent text-foreground-1 dark:text-foreground-4 dark:hocus:text-foreground-5 text-center transition-none',
            {
                'hover:text-foreground bg-overlay-accent dark:hocus:bg-overlay-accent dark:bg-overlay-accent/80': isDefault && !isSelected,
                'dark:bg-opacity-60 hocus:bg-primary dark:bg-primary-active bg-neutral-800 text-white': isSelected,
            },
            classOverride,
        ]"
        type="button"
    >
        <slot>
            {{ value }}
        </slot>
    </ButtonBase>
</template>
