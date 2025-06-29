<script setup lang="ts">
import type { PopoverSlider } from '@/types/types';

const props = withDefaults(defineProps<PopoverSlider>(), { min: 10, max: 200, step: 5 });
const model = defineModel();
</script>
<template>
    <section
        :disabled="disabled"
        :title="title ?? 'Popover Slider'"
        :class="`relative w-full flex flex-wrap gap-y-2 hover:bg-neutral-900 items-center rounded px-2 py-1.5 text-xs outline-none transition-colors data-[disabled=true]:pointer-events-none data-[disabled=true]:opacity-50 ${style ?? ''}`"
        @wheel="wheelAction"
        v-show="!hidden"
    >
        <component v-if="icon" :is="icon" class="w-4 h-4 mr-2 shrink-0" />

        <span class="text-nowrap">{{ text }}</span>
        <span class="ml-auto text-xs tracking-wide opacity-60">{{ shortcut ?? '' }}</span>
        <input
            type="range"
            @input="action"
            :min="min"
            :max="max"
            :step="step"
            v-model="model"
            :class="`w-full appearance-none flex items-center cursor-pointer bg-transparent slider volume`"
        />
    </section>
</template>
