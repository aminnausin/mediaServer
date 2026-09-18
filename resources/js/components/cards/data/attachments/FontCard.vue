<script setup lang="ts">
import type { FontInfoResource } from '@/contracts/mediaInfo';

import { formatFileSize, toFormattedDate } from '@/service/util';
import { ButtonCorner } from '@/components/cedar-ui/button';

import TablerDownload from '@/components/icons/TablerDownload.vue';

defineProps<{ data: FontInfoResource }>();
</script>
<template>
    <div class="data-card bg-surface-2 text-foreground-2 w-full space-y-2 rounded-lg p-3 shadow-sm dark:bg-white/5" v-if="data">
        <div class="flex items-center justify-between">
            <p class="text-foreground-0 truncate">{{ data.file_name }}</p>
            <ButtonCorner
                class="hover:text-primary dark:hover:text-primary-muted hover:dark:bg-surface-1 hover:bg-surface-6 size-6"
                label="Download Font"
                target="_blank"
                :use-default-style="false"
                :to="data.path"
            >
                <template #icon>
                    <TablerDownload class="size-4" />
                </template>
            </ButtonCorner>
        </div>
        <div class="flex shrink-0 justify-between">
            <p>{{ formatFileSize(data.size) }}</p>
            <p>{{ toFormattedDate(data.created_at) }}</p>
        </div>
    </div>
</template>
