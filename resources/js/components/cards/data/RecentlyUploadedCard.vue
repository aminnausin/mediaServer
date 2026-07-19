<script setup lang="ts">
import type { VideoResource } from '@/contracts/media.ts';

import { toTimeSpan } from '@/service/util';

import BrowseMediaCard from '@/components/cards/data/BrowseMediaCard.vue';

defineProps<{ media: VideoResource }>();
</script>

<template>
    <BrowseMediaCard :media="media" v-bind="$attrs">
        <span class="text-foreground-1">Uploaded {{ toTimeSpan(media.created_at, '') }}</span>
    </BrowseMediaCard>
</template>
