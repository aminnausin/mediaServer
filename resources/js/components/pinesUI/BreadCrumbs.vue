<script setup lang="ts">
import type { BreadCrumbItem } from '@/types/types';

withDefaults(defineProps<{ breadCrumbs: BreadCrumbItem[] }>(), { breadCrumbs: () => [] });
</script>
<template>
    <nav class="flex justify-between flex-1">
        <ol
            class="inline-flex items-center space-x-1 text-neutral-500 dark:text-neutral-300 [&_.active-breadcrumb]:text-neutral-600 dark:[&_.active-breadcrumb]:text-white [&_.active-breadcrumb]:font-medium sm:mb-0"
        >
            <template v-for="(breadCrumb, index) in breadCrumbs" :key="index">
                <li class="flex items-center h-full">
                    <RouterLink
                        :to="breadCrumb.url"
                        class="transition-colors inline-flex items-center p-2 py-1.5 space-x-1.5 rounded-md hover:!text-neutral-900 dark:hover:!text-white hover:ring-[0.125rem] ring-inset hover:ring-violet-400 hover:bg-white hover:dark:ring-violet-700 hover:dark:bg-primary-dark-800"
                        :class="{ 'active-breadcrumb ': index === breadCrumbs.length - 1 }"
                        :title="breadCrumb.name"
                    >
                        <component v-if="breadCrumb.icon" :is="breadCrumb.icon" class="w-3.5 h-3.5 shrink-0" />
                        <span class="capitalize max-w-32 line-clamp-1 break-all">{{ breadCrumb.name }}</span>
                    </RouterLink>
                </li>
                <svg class="w-5 h-5 text-gray-400 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" v-if="index < breadCrumbs.length - 1">
                    <g fill="none" stroke="none">
                        <path d="M10 8.013l4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    </g>
                </svg>
            </template>
        </ol>
    </nav>
</template>
