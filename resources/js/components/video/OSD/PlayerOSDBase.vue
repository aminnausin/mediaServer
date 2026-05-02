<script setup lang="ts">
import { cn } from '@aminnausin/cedar-ui';
</script>
<template>
    <div :class="cn('flex items-center justify-center rounded-full border border-neutral-700/10 bg-neutral-900/60 p-3 text-white backdrop-blur-xs', $attrs.class)">
        <slot> </slot>
    </div>
</template>
