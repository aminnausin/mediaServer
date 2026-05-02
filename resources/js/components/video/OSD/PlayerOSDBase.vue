<script setup lang="ts">
import { cn } from '@aminnausin/cedar-ui';
</script>
<template>
    <div :class="cn('flex items-center justify-center rounded-full bg-neutral-900/60 p-3 text-white', $attrs.class)">
        <slot> </slot>
    </div>
</template>
