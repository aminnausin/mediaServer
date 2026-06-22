<script setup lang="ts">
import type { FolderImageEditorProps } from '@/types/modals';
import type { SeriesResource } from '@/types/resources';

import { updateSeriesImage } from '@/service/mediaAPI';
import { useContentStore } from '@/stores/ContentStore';
import { useQueryClient } from '@tanstack/vue-query';
import { useModalStore } from '@/stores/ModalStore';
import { BaseModal } from '@/components/cedar-ui/modal';
import { toPlural } from '@/service/util';

import EditItemHeader from '@/components/headers/EditItemHeader.vue';
import EditImages from '@/components/forms/EditImages.vue';

const { updateFolderData } = useContentStore();

const queryClient = useQueryClient();

const modal = useModalStore();
const modalProps = modal.getProps<FolderImageEditorProps>();

const handleSeriesUpdate = (data: SeriesResource) => {
    updateFolderData(data);
    modal.close();

    invalidateQueries();
};

const invalidateQueries = async () => {
    try {
        if (!modalProps.queryKeys) return;
        const QueriesToInvalidate: string[][] = modalProps.queryKeys;

        QueriesToInvalidate.forEach(async (query) => {
            await queryClient.invalidateQueries({
                queryKey: query,
            });
        });
    } catch (error) {
        console.log('Failed to invalidate queries after updating a folder.', error);
    }
};
</script>

<template>
    <BaseModal>
        <template #title>{{ modalProps.title ?? 'Edit Images' }}</template>
        <template #description>
            <p>{{ modalProps.resource.title }} · {{ modalProps.images.length }} image{{ toPlural(modalProps.images.length) }}</p>
            <EditItemHeader
                v-if="modalProps.resource.edited_at && modalProps.resource.editor_id"
                :edited_at="modalProps.resource.edited_at"
                :editor_id="modalProps.resource.editor_id"
            />
        </template>
        <EditImages
            :filters="['poster', 'banner', 'preview']"
            :readOnlyTypes="['preview']"
            :primary-ids="{ poster: modalProps.resource.poster_image?.id, banner: modalProps.resource.primary_banner_id }"
            :images="modalProps.images"
            :is-audio="modalProps.isMajorityAudio"
            :submit-fn="(formData) => updateSeriesImage(modalProps.resource.id, formData)"
            @handleFinish="handleSeriesUpdate"
        />
    </BaseModal>
</template>
