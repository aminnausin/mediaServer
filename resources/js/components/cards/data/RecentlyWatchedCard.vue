<script setup lang="ts">
import type { VideoResource } from '@/contracts/media.ts';

import { toTimeSpan } from '@/service/util';

import BrowseMediaCard from '@/components/cards/data/BrowseMediaCard.vue';

defineProps<{ media: VideoResource }>();
</script>

<template>
    <BrowseMediaCard :media="media">
        <span v-if="media.progress_updated_at" class="text-foreground-1">Watched {{ toTimeSpan(media.progress_updated_at, '') }}</span>
    </BrowseMediaCard>
</template>
