<script setup lang="ts">
import type { PopoverSlider } from '@/types/types';

withDefaults(defineProps<PopoverSlider>(), { min: 10, max: 200, step: 5 });
const model = defineModel();
</script>
<template>
    <section
        :disabled="disabled"
        :title="title ?? 'Popover Slider'"
        :class="`relative flex w-full flex-wrap items-center gap-y-2 rounded px-2 py-1.5 text-xs outline-none transition-colors hover:bg-neutral-900 data-[disabled=true]:pointer-events-none data-[disabled=true]:opacity-50 ${style ?? ''}`"
        @wheel="wheelAction"
        v-show="!hidden"
    >
        <component v-if="icon" :is="icon" class="mr-2 h-4 w-4 shrink-0" />

        <span class="text-nowrap">{{ text }}</span>
        <span class="ml-auto text-xs tracking-wide opacity-60">{{ shortcut ?? '' }}</span>
        <input type="range" @input="action" :min="min" :max="max" :step="step" v-model="model" :class="`slider volume h-2 w-full`" />
    </section>
</template>
