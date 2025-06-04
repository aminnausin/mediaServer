<script setup lang="ts">
import type { RawLyricItem } from '@/types/types';

withDefaults(
    defineProps<{
        index: number;
        isActive: boolean;
        lyric: RawLyricItem;
    }>(),
    {
        index: 0,
        isActive: false,
    },
);
</script>

<template>
    <div
        :class="[
            'transition-all ease-in w-full hover:bg-neutral-800/30 ',
            lyric.time !== undefined ? 'cursor-pointer' : 'cursor-default',
            isActive ? 'bg-neutral-800/40 text-yellow-400 opacity-100 duration-300' : 'opacity-85',
        ]"
        :id="`lyric-${lyric?.time ?? index}`"
    >
        <button class="px-4 sm:px-0 py-1 sm:mx-auto sm:w-4/5 break-normal pointer-events-auto odd:font-klee-one" @click="$emit('clicked')">
            {{ lyric?.text || '-' }}
        </button>
    </div>
</template>
