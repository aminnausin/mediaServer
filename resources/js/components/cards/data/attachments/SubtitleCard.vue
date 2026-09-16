<script setup lang="ts">
import type { SubtitleInfoResource } from '@/contracts/mediaInfo';

import { toFormattedDate } from '@/service/util';

import MediaTag from '@/components/labels/MediaTag.vue';

defineProps<{ track: SubtitleInfoResource }>();

const parseSubtitleFormat = (format?: string) => {
    switch (format?.toLowerCase()) {
        case 'srt':
            return 'subrip';
        case 'vtt':
            return 'WebVTT';
        case 'ass':
        case 'ssa':
            return 'ASS';
        default:
            return 'none';
    }
};
</script>
<template>
    <div class="data-card text-foreground-2 w-full space-y-2 rounded-lg px-3 py-2 shadow-sm">
        <div class="flex items-center justify-between gap-2">
            <p class="text-foreground-0 truncate">
                <span class="text-foreground-2 font-normal">{{ track.track_id }}.</span>
                {{ track.title || 'Untitled track' }}
            </p>
            <div class="flex shrink-0 gap-1">
                <MediaTag v-if="track.is_default" class="bg-primary! text-foreground-i! dark:bg-white!">Default</MediaTag>
                <MediaTag v-if="track.is_forced" class="text-foreground-7! dark:text-foreground-0! bg-neutral-200! dark:bg-neutral-400!">Forced</MediaTag>
                <MediaTag v-if="track.external_path" class="text-foreground-7! dark:text-foreground-0! bg-neutral-200! dark:bg-neutral-400!">External</MediaTag>
            </div>
        </div>

        <div class="flex justify-between gap-2">
            <div class="flex flex-wrap gap-x-1 tracking-wide uppercase">
                <span>{{ track.language ?? 'und' }}</span>
                ·
                <span> {{ parseSubtitleFormat(track.codec) }}</span>
            </div>
            <p>
                {{ toFormattedDate(track.created_at) }}
            </p>
        </div>
    </div>
</template>
