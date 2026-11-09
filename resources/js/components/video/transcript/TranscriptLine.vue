<script setup lang="ts">
import type { TranscriptLine } from '@/components/video/transcript/transcriptParser';

import { cn } from '@aminnausin/cedar-ui';

defineProps<{ line: TranscriptLine; active: boolean; live: boolean; timestamp?: string }>();
defineEmits<{ select: [start: number] }>();
</script>

<template>
    <button
        :class="[
            'content-auto group flex w-full cursor-pointer items-start gap-2 rounded-lg p-1.5 pe-2 text-left transition-colors [contain-intrinsic-size:32px_auto]',
            live ? 'text-foreground-0 bg-white/12' : 'text-foreground-0/70 hover:text-foreground-0 hover:bg-neutral-200/40 dark:hover:bg-white/7',
        ]"
        type="button"
        @click="$emit('select', line.start)"
    >
        <span
            class="shrink-0 rounded-md px-1.5 py-0.5 font-mono text-xs tabular-nums transition-colors"
            :class="
                cn({
                    'bg-primary text-white dark:bg-white/15': live,
                    'text-foreground-0/60 group-hover:text-foreground-0/80 dark:text-foreground-0/50 bg-neutral-300/90 dark:bg-white/10': active && !live,
                    'text-foreground-0/50 group-hover:text-foreground-0/80 bg-neutral-200 dark:bg-white/5': !active,
                })
            "
        >
            {{ timestamp }}
        </span>
        <span :class="['font-dm-sans transcript-text line-clamp-3 max-h-[3lh] leading-5 duration-300 select-text', { active }]" v-html="line.text"></span>
    </button>
</template>

<style scoped>
.transcript-text {
    overflow: hidden;
    transition:
        max-height var(--tw-duration) ease-out,
        -webkit-line-clamp 0s var(--tw-duration),
        line-clamp 0s var(--tw-duration);
    transition-behavior: allow-discrete;
}

.group:hover .transcript-text,
.active.transcript-text {
    max-height: 10lh;
    -webkit-line-clamp: 10;
    line-clamp: 10;
    transition: max-height var(--tw-duration) ease-in;
}

@media (prefers-reduced-motion: reduce) {
    .transcript-text {
        transition: none;
    }
}
</style>
