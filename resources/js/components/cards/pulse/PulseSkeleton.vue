<script setup lang="ts">
import DashboardCard from '@/components/cards/layout/DashboardCard.vue';

withDefaults(
    defineProps<{
        cols?: number;
        rows?: number;
    }>(),
    {
        cols: 2,
        rows: 4,
    },
);
</script>

<template>
    <DashboardCard :rows="rows" :cols="cols" class="min-h-32">
        <div class="bg-foreground-4 h-8 w-full max-w-1/3 rounded-md"></div>
        <div class="bg-foreground-4 mt-auto h-full w-full rounded-md"></div>
    </DashboardCard>
</template>
