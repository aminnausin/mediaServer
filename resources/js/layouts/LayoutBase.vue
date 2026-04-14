<script setup lang="ts">
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { FLAGS } from '@/config/featureFlags';
import { cn } from '@aminnausin/cedar-ui';

import NavBar from '@/components/panels/NavBar.vue';

const { selectedSideBar, sideBarTarget } = storeToRefs(useAppStore());
</script>

<template>
    <main
        :class="
            cn(
                'page grid snap-y grid-cols-1 gap-3 overflow-x-clip sm:gap-6 md:h-auto lg:grid-cols-10 2xl:grid-cols-6',
                FLAGS.USE_ASYMMETRICAL_PLAYER && {
                    '2xl:grid-cols-10': true,
                    'lg:col-span-9 2xl:grid-cols-7': !selectedSideBar,
                },
            )
        "
    >
        <section
            id="left-card"
            :class="
                cn(
                    'card order-2 col-span-1 sm:scroll-mt-6 lg:order-1 lg:col-span-2 2xl:col-span-1',
                    'hidden flex-col gap-3 sm:p-3',
                    selectedSideBar && sideBarTarget === 'left-card' ? 'm:ring-1 sticky top-3 sm:top-6 lg:flex' : 'lg:invisible lg:block',
                    FLAGS.USE_ASYMMETRICAL_PLAYER && {
                        'lg:col-span-1': !selectedSideBar || sideBarTarget !== 'left-card',
                        '2xl:col-span-2': selectedSideBar && sideBarTarget === 'left-card',
                    },
                )
            "
        >
            <slot name="leftSidebar"></slot>
        </section>
        <section
            id="content-card"
            :class="
                cn(
                    'card',
                    'order-1 col-span-full w-full grow lg:order-2 lg:col-span-6 2xl:col-span-4',
                    'flex flex-col gap-3 sm:p-6 sm:pt-3',
                    FLAGS.USE_ASYMMETRICAL_PLAYER && {
                        'lg:col-span-7 2xl:col-span-7': selectedSideBar,
                        'lg:col-span-8 2xl:col-span-5': !selectedSideBar,
                    },
                )
            "
        >
            <NavBar />
            <slot name="content" class="relative z-0"></slot>
        </section>
        <section
            id="list-card"
            :class="
                cn(
                    'card order-3 col-span-1 sm:scroll-mt-6 lg:col-span-2 2xl:col-span-1',
                    'hidden flex-col gap-3 sm:p-3',
                    'z-8',
                    {
                        'hidden lg:flex': selectedSideBar && sideBarTarget === 'list-card',
                    },
                    FLAGS.USE_ASYMMETRICAL_PLAYER && {
                        '2xl:col-span-2': selectedSideBar && sideBarTarget === 'list-card',
                        'lg:col-span-1': !selectedSideBar && sideBarTarget === 'list-card',
                    },
                )
            "
        >
            <slot name="sidebar"></slot>
        </section>
    </main>
</template>

<style lang="css" scoped>
@reference '@css/app.css';
.card {
    @apply bg-surface-1 h-fit ring-gray-900/5 sm:rounded-2xl sm:shadow-xl sm:ring-1;
}

.page {
    @apply bg-surface-1 sm:bg-surface-0 xms:px-4 text-foreground-0 p-3 antialiased sm:p-6;
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

#left-card.lg\:flex {
    animation: slideInLeft var(--tw-duration, 0.2s) cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

#list-card.lg\:flex {
    animation: slideInRight var(--tw-duration, 0.2s) cubic-bezier(0.4, 0, 0.2, 1) forwards;
}
</style>
