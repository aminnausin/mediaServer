<script setup lang="ts">
import { getScreenSizeRank } from '@/service/util';

import SidebarCard from '@/components/cards/sidebar/SidebarCard.vue';
</script>

<template>
    <div class="flex flex-col gap-2 lg:py-1">
        <div class="bg-foreground-1/15 h-8 w-full max-w-32 animate-pulse truncate rounded"></div>

        <hr class="text-hr hidden lg:block" />
    </div>
    <section class="full-height-sidebar flex flex-col flex-wrap gap-2">
        <SidebarCard v-for="i in getScreenSizeRank() > 3 ? 12 : 6" :key="i" class="pointer-events-none gap-4">
            <div class="flex w-full flex-wrap items-center gap-4">
                <div class="bg-foreground-1/20 mr-auto h-4 w-32 rounded"></div>
                <div class="bg-foreground-1/10 h-3 w-24 rounded"></div>
            </div>

            <div class="flex w-full flex-wrap gap-4">
                <div class="mr-auto flex items-center gap-3">
                    <div class="bg-foreground-2/10 h-3 w-12 rounded"></div>
                    <div class="bg-foreground-2/10 h-3 w-4 rounded-full"></div>
                </div>
                <div class="bg-foreground-1/10 h-3 w-20 rounded"></div>
            </div>
        </SidebarCard>
    </section>
</template>
