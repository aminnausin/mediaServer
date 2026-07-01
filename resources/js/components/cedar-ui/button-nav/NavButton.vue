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
                'ring-0 ring-transparent ring-inset',
                {
                    'ring-primary-muted dark:bg-primary-active hocus:ring-primary outline-primary/70 ring-2 ring-inset dark:ring-0': active,
                    'hover:bg-foreground-4-hover focus-visible:bg-foreground-4-hover bg-surface-2 hover:ring-0': !active,
                },
                'dark:hover:bg-primary-dark dark:focus-visible:bg-primary-dark focus:bg-surface-2 outline-primary size-8 shrink-0 rounded-lg shadow-sm',
            )
        "
        :title="label"
    >
        <slot />
    </button-icon>
</template>
