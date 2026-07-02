<script setup lang="ts">
import type { MediaMetadataEditorProps } from '@/types/modals';
import type { VideoResource } from '@/types/resources';

import { useContentStore } from '@/stores/ContentStore';
import { useModalStore } from '@/stores/ModalStore';
import { BaseModal } from '@/components/cedar-ui/modal';

import EditItemHeader from '@/components/headers/EditItemHeader.vue';
import EditVideo from '@/components/forms/EditVideo.vue';

const { updateVideoData } = useContentStore();

const modal = useModalStore();
const modalProps = modal.getProps<MediaMetadataEditorProps>();

const handleVideoDetailsUpdate = (data: VideoResource) => {
    updateVideoData(data);
    modal.close();
};
</script>

<template>
    <BaseModal>
        <template #title>{{ modalProps.title ?? 'Edit Track/Video' }}</template>
        <template #description v-if="modalProps.mediaResource.edited_at && modalProps.mediaResource.metadata?.editor_id">
            <EditItemHeader :edited_at="modalProps.mediaResource.edited_at" :editor_id="modalProps.mediaResource.metadata.editor_id" />
        </template>
        <EditVideo v-if="modalProps.mediaResource" :video="modalProps.mediaResource" @handleFinish="handleVideoDetailsUpdate" />
    </BaseModal>
</template>
