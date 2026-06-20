<script setup lang="ts">
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { cn } from '@aminnausin/cedar-ui';

const { usingPlayerModernUI: isBackgroundEnabled } = storeToRefs(useAppStore());
</script>
<template>
    <div :class="cn('flex items-center gap-1 rounded-full transition-[padding]', { 'xsm:p-1 bg-neutral-900/60 p-0.5 backdrop-blur-xs': isBackgroundEnabled }, $attrs.class)">
        <slot></slot>
    </div>
</template>
