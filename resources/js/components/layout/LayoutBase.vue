<script setup>
import NavBar from './NavBar.vue';
import { useAppStore } from '../../stores/AppStore';
import { storeToRefs } from 'pinia';

const appStore = useAppStore();
const { selectedSideBar } = storeToRefs(appStore);
</script>

<template>
    <main class="p-6 flex gap-6 flex-wrap lg:flex-nowrap snap-y">
        <section id="left-card" class="w-full p-3 pb-6 rounded-2xl lg:w-1/6 lg:max-w-72 sm:min-w-32 shrink-0 space-y-2 scroll-mt-6 hidden lg:block">
        </section>
        <section id="content-card"
            class="bg-primary-900 dark:bg-primary-dark-900 dark:text-[#e2e0e2] shadow-xl p-6 pt-3 rounded-2xl w-full flex flex-col gap-3">
            <NavBar />
            <slot name="content"></slot>
        </section>
        <section id="list-card" :class="{ invisible: !selectedSideBar }"
            class="bg-primary-900 dark:bg-primary-dark-900 dark:text-[#e2e0e2] shadow-xl p-3 pb-6 rounded-2xl w-full lg:w-1/6 lg:max-w-72 sm:min-w-32 shrink-0 space-y-2 scroll-mt-6">
            <slot name="sidebar"></slot>
        </section>
    </main>
</template>