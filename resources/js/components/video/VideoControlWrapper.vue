<script setup lang="ts">
const isBackgroundEnabled = false;
</script>
<template>
    <section :class="['flex items-center gap-1 rounded-full', { 'bg-neutral-900/60 p-0.5 backdrop-blur-xs xs:p-1': isBackgroundEnabled }]">
        <slot></slot>
    </section>
</template>
