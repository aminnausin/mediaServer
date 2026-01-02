<script setup lang="ts">
import type { VideoResource } from '@/types/resources';

import { useContentStore } from '@/stores/ContentStore';
import { toFormattedDate } from '@/service/util';
import { useModalStore } from '@/stores/ModalStore';
import { BaseModal } from '@/components/cedar-ui/modal';

import EditVideo from '@/components/forms/EditVideo.vue';

const { updateVideoData } = useContentStore();
const modal = useModalStore();

const handleVideoDetailsUpdate = (res: any) => {
    if (res?.data?.id) updateVideoData(res.data as VideoResource);
    modal.close();
};
</script>

<template>
    <BaseModal>
        <template #title>{{ modal.props.title ?? 'Edit Track/Video' }}</template>
        <template #description v-if="modal.props.mediaResource && modal.props.mediaResource.series?.editor_id && modal.props.mediaResource.series.date_updated">
            Last edited by
            <a title="Editor profile" target="_blank" :href="`/profile/${modal.props.mediaResource.series.editor_id}`" class="hover:text-primary dark:hover:text-primary-muted">
                @{{ modal.props.mediaResource.series.editor_id }}
            </a>
            at
            {{ toFormattedDate(new Date(modal.props.mediaResource.series.date_updated)) }}
        </template>
        <EditVideo v-if="modal.props.mediaResource" :video="modal.props.mediaResource" @handleFinish="handleVideoDetailsUpdate" />
    </BaseModal>
</template>
