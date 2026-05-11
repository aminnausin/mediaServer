<script setup lang="ts">
import { cn } from '@aminnausin/cedar-ui';
</script>
<template>
    <header :class="cn('me-auto flex w-full max-w-xs flex-col sm:gap-1 xl:max-w-none', $attrs.class)">
        <slot></slot>
    </header>
</template>
