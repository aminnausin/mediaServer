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
        :class="['w-full transition-all ease-in hover:bg-neutral-800/30', isActive ? 'bg-neutral-800/40 text-yellow-400 opacity-100 duration-300' : 'opacity-85']"
        :id="`lyric-${lyric?.time ?? index}`"
    >
        <button
            :class="['pointer-events-auto px-4 py-1 break-normal sm:mx-auto sm:w-4/5 sm:px-0', lyric.time !== undefined ? 'cursor-pointer' : 'cursor-default']"
            @click="$emit('clicked')"
        >
            {{ lyric?.text || '-' }}
        </button>
    </div>
</template>
