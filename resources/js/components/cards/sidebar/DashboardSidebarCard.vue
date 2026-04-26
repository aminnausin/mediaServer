<script setup lang="ts">
import { cn } from '@aminnausin/cedar-ui';

import SidebarCard from '@/components/cards/sidebar/SidebarCard.vue';

defineProps<{ isActive?: boolean }>();
</script>

<template>
    <SidebarCard :class="cn('gap-2', 'items-center justify-between', 'overflow-hidden capitalize', 'aria-disabled:button-disabled', { 'ring-primary/70 ring-2': isActive })">
        <template #header><slot name="header" /></template>
        <template #body><slot name="body" /></template>
    </SidebarCard>
</template>
