<script setup lang="ts">
import type { RawLyricItem } from '@/types/types';

import { computed } from 'vue';
import { cn } from '@aminnausin/cedar-ui';

const emit = defineEmits<{ clicked: [play?: boolean] }>();

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

function onClick(play = false) {
    const selection = globalThis.getSelection();
    if (selection && selection.toString().length > 0) return;

    emit('clicked', play);
}

const distanceClasses = computed(() => {
    if (!props.isBlurEnabled) {
        return 'opacity-85';
    }

    switch (props.distance) {
        case 0:
            return 'opacity-100 blur-[0px]';
        case 1:
            return 'opacity-80 blur-[0px]';
        case 2:
            return 'opacity-65 blur-[0.5px]';
        case 3:
            return 'opacity-50 blur-[1px]';
        case undefined:
            return 'opacity-85 blur-[0px]';
        default:
            return 'opacity-40 blur-[2px]';
    }
});
</script>

<template>
    <div
        :class="cn('w-full transition-colors duration-300 ease-out focus-within:outline', { 'bg-neutral-800/40': isActive })"
        :id="`lyric-${lyric?.time ?? `-indexed-${index}`}`"
        :data-lyric-row="index"
        :data-active="isActive"
    >
        <button
            :class="
                cn(
                    'pointer-events-auto px-4 py-1 break-normal select-text sm:mx-auto sm:w-4/5 sm:px-0',
                    'transition-[color,opacity,filter,scale] duration-300 ease-out text-shadow-sm',
                    'focus:outline-none',
                    distanceClasses,
                    { 'cursor-pointer': lyric.time !== undefined },
                    { 'text-yellow-400 opacity-100 text-shadow-none': isActive },
                )
            "
            @click="onClick()"
            @keydown.space.prevent="(event) => !event.repeat && onClick(true)"
        >
            <span>{{ lyric?.text || '-' }}</span>
        </button>
    </div>
</template>

<style lang="css" scoped>
@media (hover: hover) and (pointer: fine) {
    div:hover {
        background-color: color-mix(in oklab, var(--color-neutral-800) 30%, transparent);
    }

    div[data-active='true']:focus-within {
        background-color: color-mix(in oklab, var(--color-neutral-800) 50%, transparent);
    }

    button:hover,
    button:focus-visible {
        opacity: 100%;
        filter: blur(0px);
    }
}
</style>
