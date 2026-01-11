<script setup lang="ts">
import type { VideoResource } from '@/types/resources';

import { useContentStore } from '@/stores/ContentStore';
import { useModalStore } from '@/stores/ModalStore';
import { BaseModal } from '@/components/cedar-ui/modal';

import EditItemHeader from '@/components/headers/EditItemHeader.vue';
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
        <template #description v-if="modal.props.mediaResource.date_updated && modal.props.mediaResource.metadata?.editor_id">
            <EditItemHeader :updated_at="modal.props.mediaResource.date_updated" :editor_id="modal.props.mediaResource.metadata.editor_id" />
        </template>
        <EditVideo v-if="modal.props.mediaResource" :video="modal.props.mediaResource" @handleFinish="handleVideoDetailsUpdate" />
    </BaseModal>
</template>
