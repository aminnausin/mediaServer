<script setup lang="ts">
import type { VideoResource } from '@/contracts/media.ts';

import { toTimeSpan } from '@/service/util';

import BrowseMediaCard from '@/components/cards/data/BrowseMediaCard.vue';

defineProps<{ video: VideoResource }>();
</script>

<template>
    <BrowseMediaCard :video="video">
        <span class="text-foreground-1">Uploaded {{ toTimeSpan(video.created_at, '') }}</span>
    </BrowseMediaCard>
</template>
