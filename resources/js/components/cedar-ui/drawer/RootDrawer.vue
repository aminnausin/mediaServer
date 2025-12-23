<script setup lang="ts">
import { cn } from '@aminnausin/cedar-ui';
</script>
<template>
    <div :class="cn('bg-surface-1 w-screen rounded-t-lg backdrop-blur-lg')">
        <slot></slot>
    </div>
</template>
