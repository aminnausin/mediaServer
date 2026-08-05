<script setup lang="ts">
import { ButtonBase } from '@/components/cedar-ui/button';
import { cn } from '@aminnausin/cedar-ui';
</script>

<template>
    <ButtonBase :use-size="false" :class="cn('rounded-full border border-neutral-700/10 bg-neutral-900/60 p-0 text-white backdrop-blur-xs', $attrs.class)">
        <slot></slot>
    </ButtonBase>
</template>
