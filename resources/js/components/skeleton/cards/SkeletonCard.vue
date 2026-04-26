<script setup lang="ts">
import SidebarCard from '@/components/cards/sidebar/SidebarCard.vue';
</script>

<template>
    <SidebarCard class="pointer-events-none min-h-20 animate-pulse justify-between gap-4 *:min-h-full">
        <div class="flex w-full flex-wrap gap-4">
            <div class="mr-auto flex items-center gap-2">
                <div class="bg-foreground-1/5 size-5 rounded"></div>
                <div class="bg-foreground-1/20 h-5 w-48 rounded"></div>
            </div>
            <div class="flex items-center gap-3">
                <div class="bg-foreground-1/10 h-3 w-32 rounded"></div>
                <div class="bg-foreground-1/10 size-4 rounded"></div>
            </div>
        </div>

        <div class="flex w-full flex-wrap gap-4">
            <div class="mr-auto flex items-center gap-3">
                <div class="bg-foreground-2/10 h-3 w-24 rounded"></div>
                <div class="bg-foreground-2/10 h-3 w-4 rounded-full"></div>
                <div class="bg-foreground-2/10 h-3 w-16 rounded"></div>
            </div>
            <div class="bg-foreground-1/10 h-3 w-40 rounded"></div>
        </div>
    </SidebarCard>
</template>
