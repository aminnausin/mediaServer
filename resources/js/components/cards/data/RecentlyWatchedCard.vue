<script setup lang="ts">
import type { VideoResource } from '@/contracts/media.ts';

import { toFormattedDate, toTimeSpan } from '@/service/util';

import BrowseMediaCard from '@/components/cards/data/explore/BrowseMediaCard.vue';

defineProps<{ media: VideoResource }>();
</script>

<template>
    <BrowseMediaCard :media="media">
        <span
            v-if="media.progress_updated_at"
            class="text-foreground-1"
            :title="`Created: ${toFormattedDate(media.progress_created_at)}\nUpdated: ${toFormattedDate(media.progress_updated_at)}`"
        >
            Watched {{ toTimeSpan(media.progress_updated_at, '') }}
        </span>
    </BrowseMediaCard>
</template>
