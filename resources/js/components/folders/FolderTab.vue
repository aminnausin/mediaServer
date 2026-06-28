<script setup lang="ts">
import { cn } from '@aminnausin/cedar-ui';
</script>
<template>
    <div :class="cn('bg-surface-2/60 ring-r-default/5 flex flex-col gap-4 rounded-xl p-3 shadow-md ring-1 dark:bg-zinc-800/64', $attrs.class)">
        <slot> </slot>
    </div>
</template>
