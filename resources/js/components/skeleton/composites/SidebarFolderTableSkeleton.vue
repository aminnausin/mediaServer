<script setup lang="ts">
import { getScreenSizeRank } from '@/service/util';

import SidebarCard from '@/components/cards/sidebar/SidebarCard.vue';
</script>

<template>
    <div class="contents">
        <SidebarCard v-for="i in getScreenSizeRank() > 3 ? 12 : 6" :key="i" class="animate-pulse p-0 lg:h-20 lg:p-3">
            <div
                :class="[
                    'bg-foreground-1/20 sm:aspect-2-3',
                    'aspect-square max-h-16 w-full rounded-t-lg object-cover',
                    'sm:max-h-none sm:w-16 sm:rounded-t-none sm:rounded-l-lg sm:shadow-md',
                    'lg:hidden',
                    'content-auto sm:[contain-intrinsic-size:auto_96px]',
                ]"
            ></div>
            <div class="flex w-full flex-1 flex-col sm:p-3 lg:contents lg:p-0">
                <div
                    :class="[
                        'flex w-full flex-1 flex-col flex-wrap gap-4 p-3 text-left',
                        'sm:min-h-18 sm:justify-between sm:p-0',
                        'lg:min-h-0 lg:flex-row lg:justify-normal lg:gap-2',
                        'justify-between',
                    ]"
                >
                    <div class="flex h-fit w-full items-center gap-2">
                        <div class="bg-foreground-1/20 mr-auto h-5 flex-1 rounded lg:h-4"></div>
                        <div class="bg-foreground-1/10 size-5 rounded lg:size-4 lg:rounded-sm"></div>
                        <div class="bg-foreground-1/10 size-5 rounded lg:size-4 lg:rounded-sm"></div>
                    </div>
                    <div class="mt-auto flex h-fit w-full justify-between gap-4">
                        <div class="bg-foreground-2/10 h-4 w-32 rounded lg:h-3 lg:flex-2"></div>
                        <div class="bg-foreground-1/10 h-4 w-20 rounded lg:h-3 lg:flex-1"></div>
                    </div>
                </div>
            </div>
        </SidebarCard>
    </div>
</template>
