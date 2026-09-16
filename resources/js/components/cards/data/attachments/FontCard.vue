<script setup lang="ts">
import type { FontInfoResource } from '@/contracts/mediaInfo';

import { formatFileSize, toFormattedDate } from '@/service/util';

defineProps<{ data: FontInfoResource }>();
</script>
<template>
    <div class="data-card text-foreground-2 w-full space-y-2 rounded-lg p-3 shadow-sm" v-if="data">
        <div class="space-y-1">
            <p class="text-foreground-0 truncate">{{ data.file_name }}</p>
        </div>
        <div class="flex shrink-0 justify-between">
            <p>{{ formatFileSize(data.size) }}</p>
            <p>{{ toFormattedDate(data.created_at) }}</p>
        </div>
    </div>
</template>
