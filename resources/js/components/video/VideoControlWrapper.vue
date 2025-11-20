<script setup lang="ts">
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';

const { usingPlayerModernUI: isBackgroundEnabled } = storeToRefs(useAppStore());
</script>
<template>
    <section :class="['flex items-center gap-1 rounded-full transition-[padding]', { 'xs:p-1 bg-neutral-900/60 p-0.5 backdrop-blur-xs': isBackgroundEnabled }]">
        <slot></slot>
    </section>
</template>
