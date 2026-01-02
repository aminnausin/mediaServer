<script setup lang="ts">
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { cn } from '@aminnausin/cedar-ui';

import NavBar from '@/components/panels/NavBar.vue';

const { selectedSideBar, sideBarTarget } = storeToRefs(useAppStore());
</script>

<template>
    <main class="page grid snap-y grid-cols-1 gap-3 overflow-x-clip sm:gap-6 md:h-auto lg:grid-cols-10 2xl:grid-cols-6">
        <section
            id="left-card"
            :class="
                cn(
                    'card order-2 col-span-1 sm:scroll-mt-6 lg:order-1 lg:col-span-2 2xl:col-span-1',
                    'hidden flex-col gap-3 sm:p-3',
                    selectedSideBar && sideBarTarget === 'left-card' ? 'visible hidden sm:ring-1 md:flex' : 'lg:invisible lg:block',
                )
            "
        >
            <slot name="leftSidebar"></slot>
        </section>
        <section id="content-card" :class="cn('card', 'order-1 col-span-full w-full grow lg:order-2 lg:col-span-6 2xl:col-span-4', 'flex flex-col gap-3 sm:p-6 sm:pt-3')">
            <NavBar />
            <slot name="content" class="relative z-0"></slot>
        </section>
        <section
            id="list-card"
            :class="
                cn('card order-3 col-span-1 sm:scroll-mt-6 lg:col-span-2 2xl:col-span-1', 'hidden flex-col gap-3 sm:p-3', {
                    'hidden md:flex': selectedSideBar && sideBarTarget === 'list-card',
                })
            "
        >
            <slot name="sidebar"></slot>
        </section>
    </main>
</template>

<style lang="css" scoped>
@reference "../../css/app.css";
.card {
    @apply bg-surface-1 h-fit ring-gray-900/5 sm:rounded-2xl sm:shadow-xl sm:ring-1;
}

.page {
    @apply bg-surface-1 sm:bg-surface-0 xms:px-4 text-foreground-0 p-3 antialiased sm:p-6;
}
</style>
