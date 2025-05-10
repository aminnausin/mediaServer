<script setup lang="ts">
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { ref, watch } from 'vue';

import NavBar from '@/components/panels/NavBar.vue';

const { selectedSideBar, sideBarTarget, scrollLock } = storeToRefs(useAppStore());

const bodyStyles = ref<Record<string, string>>({});

watch(
    () => scrollLock.value,
    async (newVal) => {
        if (newVal) {
            const scrollY = window.scrollY;

            bodyStyles.value.top = `-${scrollY}px`;
            document.body.style.overflow = 'hidden';
        } else {
            const scrollY = parseInt(bodyStyles.value.top.replaceAll('px', '')) * -1;
            bodyStyles.value.top = `0px`;
            document.body.style.overflow = 'auto';

            window.scrollTo(0, scrollY);
        }
    },
);
</script>

<template>
    <div
        class="h-screen"
        :style="bodyStyles"
        @scroll.passive="
            (e) => {
                if (scrollLock) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            }
        "
        @wheel.passive="
            (e) => {
                if (scrollLock) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            }
        "
        @touchmove.passive="
            (e) => {
                if (scrollLock) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            }
        "
    >
        <main
            class="font-nunito h-full md:h-auto grid grid-cols-1 lg:grid-cols-10 2xl:grid-cols-6 sm:p-6 gap-6 snap-y bg-primary-900 dark:bg-primary-dark-900 sm:bg-primary-950 sm:dark:bg-primary-dark-950 dark:text-white text-gray-900 antialiased overflow-x-clip"
        >
            <section
                id="left-card"
                :class="`col-span-1 lg:col-span-2 2xl:col-span-1 h-fit order-2 lg:order-1 bg-primary-900 dark:bg-primary-dark-900 sm:shadow-xl sm:rounded-2xl space-y-2 sm:scroll-mt-6 ring-gray-900/5 ${selectedSideBar && sideBarTarget === 'left-card' ? 'sm:ring-1' : 'hidden lg:block lg:invisible'}`"
            >
                <slot name="leftSidebar"></slot>
            </section>
            <section
                id="content-card"
                class="col-span-full lg:col-span-6 2xl:col-span-4 flex-grow order-1 lg:order-2 bg-primary-900 dark:bg-primary-dark-900 sm:shadow-xl p-6 pt-3 sm:rounded-2xl w-full h-fit flex flex-col gap-3 sm:ring-1 ring-gray-900/5"
            >
                <NavBar />
                <slot name="content" class="relative z-0"></slot>
            </section>
            <section
                id="list-card"
                :class="`col-span-1 lg:col-span-2 2xl:col-span-1 h-fit order-3 bg-primary-900 dark:bg-primary-dark-900  sm:shadow-xl sm:rounded-2xl space-y-2 sm:scroll-mt-6 ring-gray-900/5 ${selectedSideBar && sideBarTarget === 'list-card' ? 'sm:ring-1' : 'hidden'}`"
            >
                <slot name="sidebar"></slot>
            </section>
        </main>
    </div>
</template>

<style lang="css">
h1,
h2 {
    font-family: 'Rubik', 'sans-serif';
    font-weight: 200;
    font-optical-sizing: auto;
}
</style>
