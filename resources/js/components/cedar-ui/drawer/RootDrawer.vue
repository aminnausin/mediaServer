<script setup lang="ts">
import { cn } from '@aminnausin/cedar-ui';
</script>
<template>
    <div :class="cn('bg-surface-1 drawer-shell relative w-screen rounded-t-xl backdrop-blur-lg sm:rounded-t-lg')">
        <slot></slot>
    </div>
</template>
<style scoped lang="css">
.drawer-shell::after {
    content: '';
    position: absolute;
    inset-inline: 0;
    bottom: -200px;
    height: 200px;
    background: inherit;
}
</style>
