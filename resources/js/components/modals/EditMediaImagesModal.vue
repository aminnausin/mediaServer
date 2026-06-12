<script setup lang="ts">
import type { VideoResource } from '@/types/resources';

import { useContentStore } from '@/stores/ContentStore';
import { useModalStore } from '@/stores/ModalStore';
import { BaseModal } from '@/components/cedar-ui/modal';

import EditImages from '@/components/forms/EditImages.vue';

const { updateVideoData } = useContentStore();
const modal = useModalStore();

const handleVideoDetailsUpdate = (data: VideoResource) => {
    updateVideoData(data);
    modal.close();
};
</script>

<template>
    <BaseModal>
        <template #title>{{ modal.props.title ?? 'Edit Images' }}</template>
        <template #description v-if="modal.props.resource">
            <p>{{ modal.props.resource.title ?? 'Resource' }} · {{ modal.props.images?.length ?? 0 }} images</p>
        </template>
        <EditImages v-if="modal.props.resource && modal.props.images" :media="modal.props.resource" :images="modal.props.images" @handleFinish="handleVideoDetailsUpdate" />
    </BaseModal>
</template>
