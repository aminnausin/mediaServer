<script setup lang="ts">
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { toast } from '@/service/toaster/toastService';
import { onMounted, ref, watch } from 'vue';

import NavBar from '@/components/panels/NavBar.vue';
import { useAuthStore } from '@/stores/AuthStore';

const appStore = useAppStore();
const authStore = useAuthStore();
const { userData } = storeToRefs(authStore);
const { selectedSideBar, sideBarTarget, scrollLock } = storeToRefs(appStore);

const scrollBody = ref(null);
const userInit = ref(false);

onMounted(() => {
    window.Echo.channel(`dashboard`).listen('TaskEnded', (event: any) => {
        console.log(event);

        // toast('Hi2');
    });
});

watch(
    () => userData.value,
    () => {
        if (!userData.value?.id || userInit.value) return;
        userInit.value = true;
        // console.log('window.echo', `tasks.${userData.value.id}`);

        window.Echo.private(`tasks.${userData.value.id}`).listen('TaskEnded', (event: any) => {
            console.log('window.echo2');
            console.log(event);

            toast('Hi');
        });
    },
);
</script>

<template>
    <div class="h-screen" :class="{ '': scrollLock }" ref="scrollBody">
        <!-- overflow-y-hidden  -->
        <!-- class="flex p-6 gap-6 flex-wrap lg:flex-nowrap snap-y bg-primary-950 dark:bg-primary-dark-950 dark:text-[#e2e0e2] font-sans text-gray-900 antialiased" -->
        <main
            class="grid grid-cols-1 lg:grid-cols-6 p-6 gap-6 snap-y bg-primary-950 dark:bg-primary-dark-950 dark:text-[#e2e0e2] font-sans text-gray-900 antialiased overflow-x-clip"
        >
            <section
                id="left-card"
                :class="`col-span-1 h-fit order-2 lg:order-1 bg-primary-900 dark:bg-primary-dark-900 dark:text-[#e2e0e2] shadow-xl rounded-2xl space-y-2 scroll-mt-6 ring-gray-900/5 ${selectedSideBar && sideBarTarget === 'left-card' ? 'ring-1' : 'hidden lg:block lg:invisible'}`"
            >
                <slot name="leftSidebar"></slot>
            </section>
            <!-- class="bg-primary-900 dark:bg-primary-dark-900 dark:text-[#e2e0e2] shadow-xl p-6 pt-3 rounded-2xl w-full h-fit flex flex-col gap-3 z-20 ring-1 ring-gray-900/5" -->
            <section
                id="content-card"
                class="col-span-full lg:col-span-4 flex-grow order-1 lg:order-2 bg-primary-900 dark:bg-primary-dark-900 dark:text-[#e2e0e2] shadow-xl p-6 pt-3 rounded-2xl w-full h-fit flex flex-col gap-3 ring-1 ring-gray-900/5"
            >
                <NavBar class="z-20" />
                <slot name="content" class="relative z-0"></slot>
            </section>
            <section
                id="list-card"
                :class="`col-span-1 h-fit order-3 bg-primary-900 dark:bg-primary-dark-900 dark:text-[#e2e0e2] shadow-xl rounded-2xl space-y-2 scroll-mt-6 ring-1 ring-gray-900/5 ${selectedSideBar && sideBarTarget === 'list-card' ? 'ring-1' : 'hidden'}`"
            >
                <!-- w-full h-fit lg:w-1/6 lg:max-w-72 sm:min-w-32 shrink-0 -->
                <!-- class="bg-primary-900 dark:bg-primary-dark-900 dark:text-[#e2e0e2] shadow-xl p-3 rounded-2xl w-full h-fit lg:w-1/6 lg:max-w-72 sm:min-w-32 shrink-0 space-y-2 scroll-mt-6 z-20 ring-1 ring-gray-900/5" -->
                <slot name="sidebar"></slot>
            </section>
        </main>
    </div>
</template>
