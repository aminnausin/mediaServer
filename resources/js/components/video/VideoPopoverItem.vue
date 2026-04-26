<script setup lang="ts">
import type { PopoverItem } from '@/types/types';

import { cn } from '@aminnausin/cedar-ui';

const props = withDefaults(defineProps<PopoverItem>(), {});
</script>
<template>
    <button
        :title="title ?? 'Popover Item'"
        :class="
            cn(
                'disabled:button-disabled transition-input relative flex w-full cursor-pointer items-center rounded-md px-2 py-1.5 text-xs outline-hidden ease-in-out select-none hover:bg-neutral-900',
                { selectedStyle: selected },
                { hidden: disabled },
                style,
            )
        "
        :onclick="action"
        :data="disabled"
    >
        <component v-if="icon" :is="icon" :class="['mr-2 size-4 shrink-0', iconStyle ?? '']" />

        <span class="text-nowrap">{{ text }}</span>
        <span class="ml-auto text-xs tracking-widest opacity-60">{{ shortcut ?? '' }}</span>
        <slot name="selectedIcon">
            <component v-if="selectedIcon" :is="selectedIcon" :class="`size-4 shrink-0 ${selected ? selectedIconStyle : 'invisible'}`" />
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
                class="size-4 shrink-0"
            >
            </span>
        </slot>
    </button>
</template>
