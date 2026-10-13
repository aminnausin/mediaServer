<script setup lang="ts">
import type { FolderRelationEditorProps } from '@/types/modals';
import type { SeriesResource } from '@/types/resources';

import { useContentStore } from '@/stores/ContentStore';
import { useQueryClient } from '@tanstack/vue-query';
import { useModalStore } from '@/stores/ModalStore';
import { BaseModal } from '@/components/cedar-ui/modal';
import { toPlural } from '@/service/util';

import EditItemHeader from '@/components/headers/EditItemHeader.vue';
import EditRelations from '@/components/forms/EditRelations.vue';

const { updateFolderData } = useContentStore();

const queryClient = useQueryClient();

const modal = useModalStore();
const modalProps = modal.getProps<FolderRelationEditorProps>();

const handleSeriesUpdate = (data?: SeriesResource) => {
    if (data) updateFolderData(data);
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
        <template #title>{{ modalProps.title ?? 'Edit Relations' }}</template>
        <template #description>
            <p>{{ modalProps.resource.title }} · {{ modalProps.resource.related_series.length }} relation{{ toPlural(modalProps.resource.related_series.length ?? 0) }}</p>
            <EditItemHeader
                v-if="modalProps.resource.edited_at && modalProps.resource.editor_id"
                :edited_at="modalProps.resource.edited_at"
                :editor_id="modalProps.resource.editor_id"
            />
        </template>
        <EditRelations v-bind="modalProps" @handleFinish="handleSeriesUpdate" />
    </BaseModal>
</template>
