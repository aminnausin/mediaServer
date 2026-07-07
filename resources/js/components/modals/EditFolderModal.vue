<script setup lang="ts">
import type { FolderMetadataEditorProps } from '@/types/modals';
import type { SeriesResource } from '@/types/resources';

import { useContentStore } from '@/stores/ContentStore';
import { useQueryClient } from '@tanstack/vue-query';
import { useModalStore } from '@/stores/ModalStore';
import { BaseModal } from '@/components/cedar-ui/modal';

import EditItemHeader from '@/components/headers/EditItemHeader.vue';
import EditFolder from '@/components/forms/EditFolder.vue';

const { updateFolderData } = useContentStore();

const queryClient = useQueryClient();
const modal = useModalStore();
const modalProps = modal.getProps<FolderMetadataEditorProps>();

const handleSeriesUpdate = async (data: SeriesResource) => {
    updateFolderData(data);
    modal.close();

    if (modalProps.queryKeys) {
        invalidateQueries();
    }
};

const invalidateQueries = async () => {
    try {
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
        <template #title>Edit Folder</template>
        <template #description v-if="modalProps.cachedFolder.series?.edited_at && modalProps.cachedFolder.series.editor_id">
            <EditItemHeader :edited_at="modalProps.cachedFolder.series.edited_at" :editor_id="modalProps.cachedFolder.series.editor_id" />
        </template>
        <EditFolder v-if="modalProps.cachedFolder" :folder="modalProps.cachedFolder" @handleFinish="handleSeriesUpdate" />
    </BaseModal>
</template>
