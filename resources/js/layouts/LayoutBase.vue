<script setup lang="ts">
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';

import NavBar from '@/components/panels/NavBar.vue';

const { selectedSideBar, sideBarTarget } = storeToRefs(useAppStore());
</script>

<template>
    <main
        class="px-6 p-3 sm:p-6 md:h-auto grid grid-cols-1 lg:grid-cols-10 2xl:grid-cols-6 sm:gap-6 snap-y bg-primary-900 dark:bg-primary-dark-900 sm:bg-primary-950 sm:dark:bg-primary-dark-950 dark:text-white text-gray-900 antialiased overflow-x-clip"
    >
        <section
            id="left-card"
            :class="[
                `col-span-1 lg:col-span-2 2xl:col-span-1 h-fit order-2 lg:order-1 bg-primary-900 dark:bg-primary-dark-900 sm:shadow-xl sm:rounded-2xl space-y-2 sm:scroll-mt-6 ring-gray-900/5`,
                `${selectedSideBar && sideBarTarget === 'left-card' ? 'sm:ring-1' : 'hidden lg:block lg:invisible'}`,
                'sm:p-3 flex flex-col gap-3',
            ]"
        >
            <slot name="leftSidebar"></slot>
        </section>
        <section
            id="content-card"
            :class="[
                'col-span-full lg:col-span-6 2xl:col-span-4 flex-grow order-1 lg:order-2 bg-primary-900 dark:bg-primary-dark-900 sm:shadow-xl sm:rounded-2xl w-full h-fit sm:ring-1 ring-gray-900/5',
                'flex flex-col gap-3 sm:p-3 sm:px-6',
            ]"
        >
            <NavBar />
            <slot name="content" class="relative z-0"></slot>
        </section>
        <section
            id="list-card"
            :class="[
                `col-span-1 lg:col-span-2 2xl:col-span-1 h-fit order-3 bg-primary-900 dark:bg-primary-dark-900 sm:shadow-xl sm:rounded-2xl space-y-2 sm:scroll-mt-6 ring-gray-900/5`,
                `${selectedSideBar && sideBarTarget === 'list-card' ? 'sm:ring-1' : 'hidden'}`,
                'sm:p-3 flex flex-col gap-3',
            ]"
        >
            <slot name="sidebar"></slot>
        </section>
    </main>
</template>
