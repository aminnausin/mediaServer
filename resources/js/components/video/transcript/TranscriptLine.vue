<script setup lang="ts">
import type { TranscriptLine } from '@/components/video/transcript/transcriptParser';

import { cn } from '@aminnausin/cedar-ui';

defineProps<{ line: TranscriptLine; active: boolean; live: boolean; timestamp: string }>();
defineEmits<{ select: [start: number] }>();
</script>

<template>
    <button
        :class="[
            'content-auto group flex w-full cursor-pointer items-start gap-2 rounded-lg p-1.5 pe-2 text-left transition-colors [contain-intrinsic-size:32px_auto]',
            live ? 'bg-white/12 text-white' : 'text-white/70 hover:bg-white/7 hover:text-white',
        ]"
        type="button"
        @click="$emit('select', line.start)"
    >
        <span
            class="shrink-0 rounded-md px-1.5 py-0.5 font-mono text-xs tabular-nums transition-colors"
            :class="
                cn({
                    'bg-white/15 text-white': live,
                    'bg-white/10 text-white/50 group-hover:text-white/80': active && !live,
                    'bg-white/5 text-white/50 group-hover:text-white/80': !active,
                })
            "
        >
            {{ timestamp }}
        </span>
        <span class="font-dm-sans line-clamp-3 leading-5 select-text" role="text" v-html="line.text"></span>
    </button>
</template>
