<script setup lang="ts">
import type { PopoverSlider } from '@/types/types';

import { cn } from '@aminnausin/cedar-ui';

withDefaults(defineProps<PopoverSlider>(), { min: 10, max: 200, step: 5 });
const model = defineModel();
</script>
<template>
    <label
        :title="title ?? 'Popover Slider'"
        :class="
            cn(
                'flex w-full flex-wrap items-center gap-y-2 rounded-md px-2 py-1.5 text-xs',
                'outline-hidden transition-colors focus-within:bg-neutral-900 hover:bg-neutral-900',
                { 'button-disabled': disabled },
                style,
            )
        "
        @wheel="wheelAction"
        v-show="!hidden"
    >
        <component v-if="icon" :is="icon" class="mr-2 size-4 shrink-0" />

        <span class="text-nowrap">{{ text }}</span>
        <span class="text-foreground-1 dark ml-auto text-xs tracking-wide">{{ shortcut }}</span>
        <input
            type="range"
            @input="action"
            :min="min"
            :max="max"
            :step="step"
            v-model="model"
            :class="`slider volume h-2 w-full ring-white outline-hidden focus:ring`"
            :disabled="disabled"
        />
    </label>
</template>
