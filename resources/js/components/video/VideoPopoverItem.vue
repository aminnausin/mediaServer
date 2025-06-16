<script setup lang="ts">
import type { PopoverItem } from '@/types/types';

const props = withDefaults(defineProps<PopoverItem>(), {});
</script>
<template>
    <button
        :title="title ?? 'Popover Item'"
        :class="`${selected ? (selectedStyle ?? '') : ''}${disabled ? ' hidden' : ''} cursor-pointer relative w-full flex select-none hover:bg-neutral-900 items-center rounded px-2 py-1.5 text-xs outline-none transition-colors data-[disabled=true]:pointer-events-none data-[disabled=true]:opacity-50 ${style ?? ''}`"
        :onclick="
            () => {
                if (action) action();
            }
        "
    >
        <component v-if="icon" :is="icon" :class="['w-4 h-4 mr-2 shrink-0', iconStyle ?? '']" />

        <span class="text-nowrap">{{ text }}</span>
        <span class="ml-auto text-xs tracking-widest opacity-60">{{ shortcut ?? '' }}</span>
        <slot name="selectedIcon">
            <component v-if="selectedIcon" :is="selectedIcon" :class="`w-4 h-4 shrink-0 ${selected ? selectedIconStyle : 'invisible'}`" />
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
                class="w-4 h-4 shrink-0"
            >
            </span>
        </slot>
    </button>
</template>
