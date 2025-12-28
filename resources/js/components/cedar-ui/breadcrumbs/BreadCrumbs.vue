<script setup lang="ts">
import type { BreadCrumbItem } from '@/types/types';

withDefaults(defineProps<{ breadCrumbs: BreadCrumbItem[] }>(), { breadCrumbs: () => [] });
</script>
<template>
    <nav class="flex flex-1 justify-between">
        <ol
            class="inline-flex items-center space-x-1 text-neutral-500 sm:mb-0 dark:text-neutral-300 [&_.active-breadcrumb]:font-medium [&_.active-breadcrumb]:text-neutral-600 dark:[&_.active-breadcrumb]:text-white"
        >
            <template v-for="(breadCrumb, index) in breadCrumbs" :key="index">
                <li class="flex h-full items-center">
                    <RouterLink
                        :to="breadCrumb.url"
                        class="dark:hover:bg-primary-dark-800 inline-flex items-center space-x-1.5 rounded-md p-2 py-1.5 transition-colors ring-inset hover:bg-white hover:text-neutral-900! hover:ring-2 hover:ring-violet-400 dark:hover:text-white! dark:hover:ring-violet-700"
                        :class="{ 'active-breadcrumb': index === breadCrumbs.length - 1 }"
                        :title="breadCrumb.name"
                    >
                        <component v-if="breadCrumb.icon" :is="breadCrumb.icon" class="h-3.5 w-3.5 shrink-0" />
                        <span class="line-clamp-1 max-w-32 break-all capitalize">{{ breadCrumb.name }}</span>
                    </RouterLink>
                </li>
                <svg class="h-5 w-5 shrink-0 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" v-if="index < breadCrumbs.length - 1">
                    <g fill="none" stroke="none">
                        <path d="M10 8.013l4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    </g>
                </svg>
            </template>
        </ol>
    </nav>
</template>
