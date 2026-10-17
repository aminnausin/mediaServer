<script setup lang="ts">
import type { FolderRelationEditorProps } from '@/types/modals';
import type { SeriesResource } from '@/types/resources';

import { useGetSeriesRelations } from '@/service/folder/queries';
import { useQueryClient } from '@tanstack/vue-query';
import { useModalStore } from '@/stores/ModalStore';
import { BaseModal } from '@/components/cedar-ui/modal';
import { toPlural } from '@/service/util';
import { computed } from 'vue';

import EditItemHeader from '@/components/headers/EditItemHeader.vue';
import EditRelations from '@/components/forms/EditRelations.vue';

const queryClient = useQueryClient();

const modal = useModalStore();
const modalProps = modal.getProps<FolderRelationEditorProps>();

const seriesId = computed(() => modalProps.seriesId);

const { data: relations } = useGetSeriesRelations(seriesId);

const handleSeriesUpdate = (_?: SeriesResource) => {
    modal.close();
    invalidateQueries();
};

const invalidateQueries = async () => {
    try {
        const extraQueries = modalProps.queryKeys ?? [];
        const QueriesToInvalidate: string[][] = [...extraQueries, ['series-relations']];
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
            <p>{{ modalProps.resource.title }} · {{ relations?.length ?? 0 }} relation{{ toPlural(relations?.length ?? 0) }}</p>
            <EditItemHeader
                v-if="modalProps.resource.edited_at && modalProps.resource.editor_id"
                :edited_at="modalProps.resource.edited_at"
                :editor_id="modalProps.resource.editor_id"
            />
        </template>
        <EditRelations v-bind="modalProps" @handleFinish="handleSeriesUpdate" :relations="relations ?? []" />
    </BaseModal>
</template>
