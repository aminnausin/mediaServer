<script setup>
    import NavBar from './NavBar.vue';
    import { useAuthStore } from '../../stores/AuthStore'
    import { storeToRefs } from 'pinia';

    const authStore = useAuthStore();

    const {selectedSideBar} = storeToRefs(authStore);
</script>

<template>
    <main class="p-6 flex gap-6 flex-row-reverse flex-wrap-reverse lg:flex-nowrap snap-y">
        <section id="list-card" :class="{ invisible: !selectedSideBar}" class="dark:bg-neutral-900 dark:text-[#e2e0e2] shadow-xl p-3 pb-6 rounded-2xl w-full lg:w-1/6 lg:max-w-72 shrink-0 space-y-2 scroll-mt-6">
            <slot name="sidebar"></slot>
        </section>
        <section id="content-card" class="dark:bg-neutral-900 dark:text-[#e2e0e2] shadow-xl p-6 pt-3 rounded-2xl w-full">
            <NavBar/>
            <slot name="content"></slot>
        </section>
        <section id="left-card" class="w-full lg:w-72 shrink-0 hidden lg:block">
        </section>
    </main>
</template>