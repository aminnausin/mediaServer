<script setup lang="ts">
import type { PopoverItem } from '@/types/types';

import { cn } from '@aminnausin/cedar-ui';

defineOptions({ inheritAttrs: false });

const props = withDefaults(defineProps<PopoverItem>(), {});
</script>
<template>
    <div v-if="divider" class="bg-hr dark:bg-hr/30 my-1 h-px shrink-0 group-[.pe-0\.5]/popover-items:-me-0.5" />

    <div v-else class="px-1">
        <button
            v-bind="$attrs"
            :title="title ?? ''"
            :class="
                cn(
                    'disabled:button-disabled transition-input flex w-full cursor-pointer items-center rounded-md px-2 py-1.5 text-xs ease-in-out select-none',
                    'ring-white outline-hidden ring-inset hover:bg-neutral-900 focus:bg-neutral-950 focus:outline-none focus-visible:ring',
                    { selectedStyle: selected },
                    { hidden: disabled },
                    style,
                    $attrs.class,
                )
            "
            :onclick="action"
            :data="disabled"
        >
            <component v-if="icon" :is="icon" :class="cn('mr-2 size-4 shrink-0', iconStyle)" />

            <span class="truncate text-nowrap">{{ text }}</span>
            <span class="ml-auto text-xs tracking-widest opacity-60">{{ shortcut ?? '' }}</span>
            <slot name="selectedIcon">
                <component
                    v-if="selectedIcon"
                    :is="selectedIcon"
                    :class="cn('ms-1 size-4 shrink-0', { 'text-primary-muted dark:text-primary': selected }, selected ? selectedIconStyle : 'invisible')"
                />
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
    </div>
</template>
