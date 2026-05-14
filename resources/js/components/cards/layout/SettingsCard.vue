<script setup lang="ts">
import { cn } from '@aminnausin/cedar-ui';
</script>
<template>
    <section :class="cn('dark:bg-primary-dark-800/70 flex flex-wrap gap-3 rounded-xl bg-white p-3 shadow-md ring-1 ring-gray-900/5 sm:flex-nowrap sm:p-6', $attrs.class)">
        <slot name="heading"></slot>

        <slot name="content"></slot>

        <slot></slot>
    </section>
</template>
