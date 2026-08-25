<script setup lang="ts">
import type { RawLyricItem } from '@/types/types';

import { computed } from 'vue';
import { cn } from '@aminnausin/cedar-ui';

const emit = defineEmits<(e: 'clicked') => void>();

const props = withDefaults(
    defineProps<{
        index: number;
        lyric: RawLyricItem;
        isActive: boolean;
        distance?: number;
        isBlurEnabled?: boolean;
    }>(),
    {
        index: 0,
        isActive: false,
    },
);

function onClick() {
    const selection = globalThis.getSelection();
    if (selection && selection.toString().length > 0) return;

    emit('clicked');
}

const distanceClasses = computed(() => {
    if (!props.isBlurEnabled) {
        return 'opacity-85';
    }

    switch (props.distance) {
        case 0:
            return 'opacity-100 blur-none';
        case 1:
            return 'opacity-80 blur-none';
        case 2:
            return 'opacity-65 blur-[0.5px]';
        case 3:
            return 'opacity-50 blur-[1px]';
        case undefined:
            return 'opacity-85 blur-none';
        default:
            return 'opacity-40 blur-[2px]';
    }
});
</script>

<template>
    <div
        :class="cn('w-full transition-colors duration-300 ease-out', { 'bg-neutral-800/40': isActive })"
        :id="`lyric-${lyric?.time ?? `-indexed-${index}`}`"
        :data-lyric-row="index"
    >
        <button
            :class="
                cn(
                    'pointer-events-auto px-4 py-1 break-normal select-text sm:mx-auto sm:w-4/5 sm:px-0',
                    'drop-shadow-sm transition-[color,opacity,filter,scale] duration-300 ease-out',
                    distanceClasses,
                    { 'cursor-pointer': lyric.time !== undefined },
                    { 'text-yellow-400 opacity-100 drop-shadow-none': isActive },
                )
            "
            @click="onClick"
        >
            <span>{{ lyric?.text || '-' }}</span>
        </button>
    </div>
</template>

<style lang="css" scoped>
@media (hover: hover) and (pointer: fine) {
    div:hover {
        background-color: color-mix(in oklab, var(--color-neutral-800) /* oklch(26.9% 0 0) = #262626 */ 30%, transparent);
    }
    button:hover {
        opacity: 100%;
        filter: blur(0);
    }
}
</style>
