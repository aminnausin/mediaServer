<script setup lang="ts">
import { cn } from '@aminnausin/cedar-ui';
</script>

<template>
    <div :class="cn('xs:flex-row xs:justify-end relative flex w-full flex-col-reverse gap-2', $attrs.class)">
        <slot />
    </div>
</template>
