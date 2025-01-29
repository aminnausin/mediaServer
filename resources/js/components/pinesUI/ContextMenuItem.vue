<script setup lang="ts">
import type { ContextMenuItem } from '@/types/types';

import { RouterLink } from 'vue-router';
const props = withDefaults(defineProps<ContextMenuItem>(), {});
</script>
<template>
    <a
        v-if="external && url"
        :href="url"
        target="_blank"
        :class="`${selected ? (selectedStyle ?? 'font-bold text-violet-500') : ''} cursor-pointer relative w-full flex select-none hover:bg-neutral-100 dark:hover:bg-neutral-900 items-center rounded px-2 py-1.5 text-sm outline-none transition-colors data-[disabled=true]:pointer-events-none data-[disabled=true]:opacity-50 ${style ?? ''}`"
    >
        <slot name="icon">
            <component v-if="icon" :is="icon" class="w-4 h-4 mr-2" />
            <span
                v-else
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="w-4 h-4 mr-2"
            >
            </span>
        </slot>
        <span class="text-nowrap">{{ text }}</span
        ><span class="ml-auto text-xs tracking-widest opacity-60">{{ shortcut ?? '' }}</span>
    </a>
    <RouterLink
        v-else-if="url"
        :to="disabled ? '' : url"
        :data-disabled="disabled"
        :class="`${selected ? (selectedStyle ?? 'font-bold text-violet-500') : ''} cursor-pointer relative w-full flex select-none hover:bg-neutral-100 dark:hover:bg-neutral-900 items-center rounded px-2 py-1.5 text-sm outline-none transition-colors data-[disabled=true]:pointer-events-none data-[disabled=true]:opacity-50 ${style ?? ''}`"
    >
        <slot name="icon">
            <component v-if="icon" :is="icon" class="w-4 h-4 mr-2" />
            <span
                v-else
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="w-4 h-4 mr-2"
            >
            </span>
        </slot>
        <span class="text-nowrap">{{ text }}</span>
        <span class="ml-auto text-xs tracking-widest opacity-60">{{ shortcut ?? '' }}</span>
    </RouterLink>
    <button
        v-else
        :disabled="disabled"
        :class="`${selected ? (selectedStyle ?? 'font-bold text-violet-500') : ''} cursor-pointer relative w-full flex select-none hover:bg-neutral-100 dark:hover:bg-neutral-900 items-center rounded px-2 py-1.5 text-sm outline-none transition-colors data-[disabled=true]:pointer-events-none data-[disabled=true]:opacity-50 ${style ?? ''}`"
        :onclick="
            () => {
                if (action) action();
            }
        "
    >
        <slot name="icon">
            <component v-if="icon" :is="icon" class="w-4 h-4 mr-2" />
            <span
                v-else
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="w-4 h-4 mr-2"
            >
            </span>
        </slot>
        <span class="text-nowrap">{{ text }}</span>
        <span class="ml-auto text-xs tracking-widest opacity-60">{{ shortcut ?? '' }}</span>
    </button>
</template>
