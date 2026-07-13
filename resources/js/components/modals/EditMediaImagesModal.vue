<script setup lang="ts">
import type { MediaImageEditorProps } from '@/types/modals';
import type { VideoResource } from '@/types/resources';

import { updateMediaImage } from '@/service/mediaAPI';
import { useContentStore } from '@/stores/ContentStore';
import { useModalStore } from '@/stores/ModalStore';
import { BaseModal } from '@/components/cedar-ui/modal';
import { MediaType } from '@/types/types';
import { toPlural } from '@/service/util';

import EditItemHeader from '@/components/headers/EditItemHeader.vue';
import EditMediaModal from '@/components/modals/EditMediaModal.vue';
import EditImages from '@/components/forms/EditImages.vue';

const { updateVideoData } = useContentStore();

const modal = useModalStore();
const modalProps = modal.getProps<MediaImageEditorProps>();

const handleVideoDetailsUpdate = (data: VideoResource) => {
    updateVideoData(data);
    modal.close();
};
</script>

<template>
    <BaseModal>
        <template #title>{{ modalProps.title ?? 'Edit Images' }}</template>
        <template #description>
            <p>{{ modalProps.resource.title }}: {{ modalProps.images.length }} image{{ toPlural(modalProps.images.length) }}</p>
            <EditItemHeader
                v-if="modalProps.resource.edited_at && modalProps.resource.editor_id"
                :edited_at="modalProps.resource.edited_at"
                :editor_id="modalProps.resource.editor_id"
            />
        </template>
        <EditImages
            v-if="modalProps.resource && modalProps.images"
            :filters="['poster', 'preview']"
            :readOnlyTypes="['preview']"
            :primary-ids="{ poster: modalProps.resource.poster_image?.id }"
            :generatable-filters="{
                preview: {
                    url: `/${modalProps.libraryId}/${modalProps.mediaResource.folder_id}?video=${modalProps.mediaResource.id}&preview=1`,
                    text: 'Generate preview in new tab',
                },
            }"
            :images="modalProps.images"
            :is-audio="modalProps.resource.media_type === MediaType.AUDIO"
            :submit-fn="(formData) => updateMediaImage(modalProps.resource.id, formData)"
            :open-editor-fn="
                () => {
                    const metadataInfo = { titleTooltip: `UUID: ${modalProps.resource.uuid}` };

                    modal.open(EditMediaModal, {
                        title: `Edit ${modalProps.resource.media_type === MediaType.AUDIO ? 'Track' : 'Video'} Metadata`,
                        mediaResource: modalProps.mediaResource,
                        ...metadataInfo,
                    });
                }
            "
            @handleFinish="handleVideoDetailsUpdate"
        />
    </BaseModal>
</template>
