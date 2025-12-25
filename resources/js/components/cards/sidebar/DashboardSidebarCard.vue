<script setup lang="ts">
import { cn } from '@aminnausin/cedar-ui';

import SidebarCard from './SidebarCard.vue';
</script>

<template>
    <SidebarCard
        :class="
            cn(
                'gap-2',
                'items-center justify-between',
                'hover:bg-primary-800 overflow-hidden bg-white capitalize',
                'ring-primary hover:ring-primary/50 ring-inset hover:ring-2',
                'aria-disabled:button-disabled',
            )
        "
    >
        <template #header><slot name="header" /></template>
        <template #body><slot name="body" /></template>
    </SidebarCard>
</template>
