<script setup lang="ts">
import type { GenericSortOption } from '@/types/types';
import type { VideoResource } from '@/contracts/media';
import type { ComputedRef } from 'vue';
import type { SortDir } from '@aminnausin/cedar-ui';

import { mediaSortingOptions } from '@/constants/sortingOptions';
import { useContentStore } from '@/stores/ContentStore';
import { useModalStore } from '@/stores/ModalStore';
import { storeToRefs } from 'pinia';
import { MediaType } from '@/types/types';
import { cn, toast } from '@aminnausin/cedar-ui';
import { computed } from 'vue';

import EditMediaModal from '@/components/modals/EditMediaModal.vue';
import ShareModal from '@/components/modals/ShareModal.vue';
import TableBase from '@/components/cedar-ui/table/TableBase.vue';
import VideoCard from '@/components/cards/data/VideoCard.vue';
import FolderTab from '@/components/folders/FolderTab.vue';

const { searchQuery, stateFilteredPlaylist, stateDirectory, stateVideo, stateFolder, currentMediaIndex, isLoadingContent } = storeToRefs(useContentStore());
const { playlistSort } = useContentStore();

const props = defineProps<{ useBackground?: boolean }>();
const modal = useModalStore();

const sortingOptions = computed(() => mediaSortingOptions(stateFolder.value)) satisfies ComputedRef<GenericSortOption<VideoResource>[]>; // Idk what the point of using satisfies is

const mediaTypeDescription = computed(() => {
    return stateVideo.value?.metadata?.media_type === MediaType.AUDIO || stateFolder.value?.is_majority_audio ? 'Track' : 'Video';
});

const handleSort = (column: keyof VideoResource = 'file_modified_at', dir: SortDir = 1) => {
    playlistSort({ column, dir });
};

const handleVideoAction = (e: Event, id: number, action: 'edit' | 'share' | 'download') => {
    if (!stateFolder.value?.videos) return;

    const mediaResource = stateFolder.value.videos.find((video: VideoResource) => video.id === id);

    if (!mediaResource) {
        toast.error('File not found');
        return;
    }

    switch (action) {
        case 'edit': {
            const metadataInfo = mediaResource.metadata ? { titleTooltip: `UUID: ${mediaResource.metadata.uuid}` } : {};

            modal.open(EditMediaModal, { title: `Edit ${mediaTypeDescription.value} Metadata`, mediaResource: mediaResource, ...metadataInfo });
            break;
        }
        case 'share':
            modal.open(ShareModal, {
                title: `Share ${mediaTypeDescription.value}`,
                shareLink: encodeURI(`${document.location.origin}/${stateDirectory.value.name}/${stateFolder.value.title}?video=${mediaResource.id}`),
                defaultTimestamp: 0,
            });
            break;
        default:
            toast.error('Option Unavailable', { description: `You cannot ${action} this file.` });
            break;
    }
};
</script>

<template>
    <FolderTab :class="cn('flex-1', { 'bg-transparent p-0 shadow-none ring-0': !useBackground })">
        <TableBase
            ref="mediaTable"
            :class="'h-full flex-1'"
            :data="stateFilteredPlaylist"
            :row="VideoCard"
            :otherAction="handleVideoAction"
            :loading="isLoadingContent"
            :useToolbar="true"
            :sortAction="handleSort"
            :sortingOptions="sortingOptions.filter((s) => !s.hidden)"
            :selectedID="stateVideo?.id"
            :startAscending="true"
            :currentIndex="currentMediaIndex"
            v-model="searchQuery"
        />
    </FolderTab>
</template>
