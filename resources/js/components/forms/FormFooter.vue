<script setup lang="ts">
import { cn } from '@aminnausin/cedar-ui';
</script>
<template>
    <div :class="cn('flex h-8 flex-wrap justify-end gap-2', $attrs.class)">
        <slot />
    </div>
</template>
