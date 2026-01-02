<script setup lang="ts">
import { ButtonIcon } from '../button';
import { cn } from '@aminnausin/cedar-ui';

const props = defineProps<{
    label?: string;
    colour?: string;
    active?: boolean;
    url?: string;
    href?: string;
}>();
</script>

<template>
    <button-icon
        :id="'link-nav-' + label"
        :to="url"
        :href="href"
        :variant="'transparent'"
        :class="cn('hocus:bg-foreground-4-hover hocus:dark:bg-primary-dark hover:ring-primary bg-surface-2 size-8 shrink-0 rounded-lg shadow-lg dark:hover:ring-0', {})"
        :title="label"
    >
        <slot></slot>
    </button-icon>
</template>
