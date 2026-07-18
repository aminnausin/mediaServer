<script setup lang="ts">
import type { VideoResource } from '@/contracts/media.ts';

import { toTimeSpan } from '@/service/util';

import BrowseMediaCard from '@/components/cards/data/BrowseMediaCard.vue';

defineProps<{ video: VideoResource }>();
</script>

<template>
    <BrowseMediaCard :video="video">
        <span v-if="video.progress_updated_at" class="text-foreground-1">Watched {{ toTimeSpan(video.progress_updated_at, '') }}</span>
    </BrowseMediaCard>
</template>
