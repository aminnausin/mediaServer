<script setup lang="ts">
import { cn } from '@aminnausin/cedar-ui';
</script>
<template>
    <div :class="cn('bg-surface-2/60 ring-r-default/5 dark:bg-primary-dark-800/70 flex flex-col gap-4 rounded-xl p-3 shadow-md ring-1', $attrs.class)">
        <slot> </slot>
    </div>
</template>
