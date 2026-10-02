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
            return format;
    }
};
</script>
<template>
    <div class="data-card bg-surface-2 text-foreground-2 w-full space-y-2 rounded-lg p-3 shadow-sm dark:bg-white/5">
        <div class="flex items-center justify-between gap-2">
            <p class="text-foreground-0 truncate">
                <span class="text-foreground-2 font-normal">{{ track.track_id }}.</span>
                {{ track.title || 'Untitled track' }}
            </p>
            <div class="flex shrink-0 gap-1 *:text-xs">
                <MediaTag v-if="track.is_default" class="bg-primary! text-foreground-i! dark:bg-white!">Default</MediaTag>
                <MediaTag v-if="track.is_forced" class="text-foreground-0 pointer-events-none py-0.5 text-xs">Forced</MediaTag>
                <MediaTag v-if="track.external_path" class="text-foreground-0 pointer-events-none py-0.5 text-xs">External</MediaTag>
            </div>
        </div>

        <div class="flex justify-between gap-2">
            <div class="flex flex-wrap gap-x-1 tracking-wide uppercase">
                <span>{{ track.language ?? 'und' }}</span>
                |
                <span> {{ parseSubtitleFormat(track.codec) }}</span>
            </div>
            <p>
                {{ toFormattedDate(track.created_at) }}
            </p>
        </div>
    </div>
</template>
