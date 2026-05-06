<script setup lang="ts">
import SettingsCard from '@/components/cards/layout/SettingsCard.vue';
</script>

<template>
    <SettingsCard class="animate-pulse flex-col gap-6">
        <div>
            <div class="mb-1 h-6 w-32 rounded bg-neutral-200 dark:bg-neutral-700" />
            <div class="h-5 w-72 rounded bg-neutral-200/70 dark:bg-neutral-700/50" />
        </div>

        <div v-for="i in 2" :key="i" class="flex flex-col gap-2">
            <div class="h-5 w-96 max-w-2/3 rounded bg-neutral-200 dark:bg-neutral-700" />
            <div class="h-4 w-64 max-w-3/5 rounded bg-neutral-200 dark:bg-neutral-700" />
            <div class="h-9 w-full rounded bg-neutral-200/70 dark:bg-neutral-700/50" />
        </div>

        <div class="flex justify-end gap-2">
            <div class="h-8 w-16 rounded-md bg-neutral-300 dark:bg-neutral-700" />
            <div class="h-8 w-14 rounded-md bg-neutral-300 dark:bg-neutral-700" />
        </div>
    </SettingsCard>
</template>
