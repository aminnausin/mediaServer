<script setup>
    import NavBar from './NavBar.vue';
</script>

<template>
    <main class="p-6 flex gap-6 flex-row-reverse flex-wrap-reverse lg:flex-nowrap snap-y">
        <section id="list-card" class="invisible dark:bg-neutral-900 shadow-xl p-3 pb-6 rounded-2xl light-mode w-full lg:w-1/6 lg:max-w-72 shrink-0 space-y-2 scroll-mt-6">
            <slot name="sidebar"></slot>
        </section>
        <section id="content-card" class=" dark:bg-neutral-900 shadow-xl p-6 pt-3 rounded-2xl light-mode w-full">
            <NavBar/>
            <slot name="content"></slot>
        </section>
        <section id="left-card" class="w-full lg:w-72 shrink-0 hidden lg:block">
        </section>
    </main>
</template>