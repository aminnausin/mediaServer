<script setup lang="ts">
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

const handleSeriesUpdate = async (res: any) => {
    if (res?.data?.id) updateFolderData(res.data as SeriesResource);
    modal.close();

    if (modal.props.queryKeys) {
        invalidateQueries();
    }
};

const invalidateQueries = async () => {
    try {
        const QueriesToInvalidate: string[][] = modal.props.queryKeys;
        console.log(modal.props, QueriesToInvalidate);

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
        <template #description v-if="modal.props.cachedFolder.series.edited_at && modal.props.cachedFolder.series.editor_id">
            <EditItemHeader :edited_at="modal.props.cachedFolder.series.edited_at" :editor_id="modal.props.cachedFolder.series.editor_id" />
        </template>
        <EditFolder v-if="modal.props.cachedFolder" :folder="modal.props.cachedFolder" @handleFinish="handleSeriesUpdate" />
    </BaseModal>
</template>
