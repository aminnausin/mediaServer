<script setup lang="ts">
import type { MediaInfoResource } from '@/contracts/mediaInfo';
import type { VideoResource } from '@/contracts/media.ts';

import SubtitleCard from '@/components/cards/data/attachments/SubtitleCard.vue';

defineProps<{ data: VideoResource; mediaInfo: MediaInfoResource }>();
</script>

<template>
    <div class="flex flex-col gap-2 text-sm">
        <SubtitleCard v-for="track in mediaInfo.subtitles" :key="track.id" :track="track" />
    </div>
</template>
