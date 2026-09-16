<script setup lang="ts">
import type { FontInfoResource, MediaInfoResource } from '@/contracts/mediaInfo';
import type { VideoResource } from '@/contracts/media.ts';
import type { SortDir } from '@aminnausin/cedar-ui';

import { computed, ref } from 'vue';
import { sortObject } from '@aminnausin/cedar-ui';
import { TableBase } from '@/components/cedar-ui/table';

import FontCard from '@/components/cards/data/attachments/FontCard.vue';

const props = defineProps<{ data: VideoResource; mediaInfo: MediaInfoResource }>();
const search = ref('');
const sortKey = ref<keyof FontInfoResource>('file_name');
const sortDir = ref<SortDir>(1);

const handleSort = (key: keyof FontInfoResource, dir: SortDir) => {
    sortKey.value = key;
    sortDir.value = dir;
};

const sortingOptions = ref<{ title: string; value: keyof FontInfoResource }[]>([
    {
        title: 'File Name',
        value: 'file_name',
    },
    {
        title: 'Size',
        value: 'size',
    },
    {
        title: 'Date Created',
        value: 'created_at',
    },
]);

const filteredFonts = computed<FontInfoResource[]>(() => {
    return [...(search.value ? props.mediaInfo.fonts.filter((font) => font.file_name?.toLowerCase().includes(search.value?.toLowerCase())) : props.mediaInfo.fonts)].sort(
        sortObject<FontInfoResource>(sortKey.value, sortDir.value, ['created_at', 'updated_at']),
    );
});
</script>

<template>
    <div class="flex flex-col gap-2 text-sm">
        <TableBase
            ref="fontTable"
            :class="'w-full flex-1'"
            :pagination-class="'sm:justify-around! xl:justify-between!'"
            :table-styles="'w-full'"
            :data="filteredFonts"
            :row="FontCard"
            :useToolbar="true"
            :force-vertical-toolbar="true"
            :startAscending="true"
            :sort-action="handleSort"
            :sorting-options="sortingOptions"
            v-model="search"
        />

        <p v-if="!filteredFonts.length" class="text-foreground-2 py-4 text-center text-xs tracking-wider uppercase">No fonts match "{{ search }}"</p>
    </div>
</template>
