<script setup lang="ts">
import type { BreadCrumbItem } from '@aminnausin/cedar-ui';

import { ButtonBase } from '@/components/cedar-ui/button';
import { cn } from '@aminnausin/cedar-ui';

withDefaults(defineProps<{ breadCrumbs: BreadCrumbItem[] }>(), { breadCrumbs: () => [] });
</script>
<template>
    <nav class="w-full min-w-1/2 flex-1 overflow-hidden" aria-label="Breadcrumb">
        <ol class="text-foreground-2 dark:text-foreground-4 scrollbar-minimal flex items-center gap-1 overflow-x-auto whitespace-nowrap">
            <template v-for="(breadCrumb, index) in breadCrumbs" :key="index">
                <li :class="cn('flex min-w-12 shrink-4 items-center', { 'shrink sm:shrink-0': index === breadCrumbs.length - 1 })">
                    <ButtonBase
                        :to="breadCrumb.url"
                        :class="
                            cn(
                                'inline-flex max-w-42 min-w-4 items-center gap-1.5 rounded-md px-2 py-1.5',
                                'hocus:bg-surface-2 hocus:text-foreground-0',
                                'hover:ring-primary-muted focus:ring-primary hocus:ring-2 ring-1 ring-transparent ring-inset',
                                'focus:outline-none',
                                index === breadCrumbs.length - 1 && 'text-foreground-1 dark:text-foreground font-medium',
                            )
                        "
                        :title="breadCrumb.name"
                        :aria-current="index === breadCrumbs.length - 1 ? 'page' : undefined"
                        :use-size="false"
                    >
                        <component v-if="breadCrumb.icon" :is="breadCrumb.icon" class="size-3.5 shrink-0" />
                        <span class="truncate capitalize">
                            {{ breadCrumb.name }}
                        </span>
                    </ButtonBase>
                </li>

                <li v-if="index < breadCrumbs.length - 1" class="shrink-0" aria-hidden="true">
                    <svg class="size-5 text-neutral-400 dark:text-neutral-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                        <path d="m10 8 4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </li>
            </template>
        </ol>
    </nav>
</template>

<style scoped>
a {
    text-decoration: none;
}
</style>
