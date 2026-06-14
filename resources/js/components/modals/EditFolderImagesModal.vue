<script setup lang="ts">
import type { SeriesImageEditorProps } from '@/types/modals';
import type { SeriesResource } from '@/types/resources';

import { updateSeriesImage } from '@/service/mediaAPI';
import { useContentStore } from '@/stores/ContentStore';
import { useQueryClient } from '@tanstack/vue-query';
import { useModalStore } from '@/stores/ModalStore';
import { BaseModal } from '@/components/cedar-ui/modal';

import EditItemHeader from '@/components/headers/EditItemHeader.vue';
import EditImages from '@/components/forms/EditImages.vue';

const { updateFolderData } = useContentStore();

const queryClient = useQueryClient();

const modal = useModalStore();
const modalProps = modal.getProps<SeriesImageEditorProps>();

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
            <p>{{ modalProps.resource.title }} · {{ modalProps.images.length }} images</p>
            <EditItemHeader
                v-if="modalProps.resource.edited_at && modalProps.resource.editor_id"
                :edited_at="modalProps.resource.edited_at"
                :editor_id="modalProps.resource.editor_id"
            />
        </template>
        <EditImages
            :filters="['poster', 'preview']"
            :readOnlyTypes="['preview']"
            :primary-ids="{ poster: modalProps.resource.poster_image?.id }"
            :images="modalProps.images"
            :is-audio="modalProps.isMajorityAudio"
            :submit-fn="(formData) => updateSeriesImage(modalProps.resource.id, formData)"
            @handleFinish="handleSeriesUpdate"
        />
    </BaseModal>
</template>
