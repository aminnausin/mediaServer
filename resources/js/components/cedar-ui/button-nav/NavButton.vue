<script setup lang="ts">
import { ButtonIcon } from '../button';
import { cn } from '@aminnausin/cedar-ui';

const props = defineProps<{
    label?: string;
    colour?: string;
    active: boolean;
}>();
</script>
<template>
    <button-icon
        :id="'btn-nav-' + label"
        :variant="'ghost'"
        :class="
            cn(
                {
                    'ring-primary dark:bg-primary-active hocus:ring-primary/70 ring-2 ring-inset dark:ring-0': active,
                    'hocus:bg-foreground-4-hover bg-surface-2 hover:ring-0': !active,
                },
                'dark:hocus:bg-primary-dark focus:bg-surface-2 size-8 shrink-0 rounded-lg shadow-lg',
            )
        "
        :title="label"
    >
        <slot />
    </button-icon>
</template>
