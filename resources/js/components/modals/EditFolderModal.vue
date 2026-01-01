<script setup lang="ts">
import type { SeriesResource } from '@/types/resources';

import { useContentStore } from '@/stores/ContentStore';
import { toFormattedDate } from '@/service/util';
import { useModalStore } from '@/stores/ModalStore';
import { BaseModal } from '@/components/cedar-ui/modal';

import EditFolder from '@/components/forms/EditFolder.vue';

const { updateFolderData } = useContentStore();
const modal = useModalStore();

const handleSeriesUpdate = async (res: any) => {
    if (res?.data?.id) updateFolderData(res.data as SeriesResource);
    modal.close();
};
</script>

<template>
    <BaseModal>
        <template #title>Edit Folder</template>
        <template #description v-if="modal.props.cachedFolder && modal.props.cachedFolder.series?.editor_id && modal.props.cachedFolder.series.date_updated">
            Last edited by
            <a title="Editor profile" target="_blank" :href="`/profile/${modal.props.cachedFolder.series.editor_id}`" class="hover:text-primary dark:hover:text-primary-muted">
                @{{ modal.props.cachedFolder.series.editor_id }}
            </a>
            at
            {{ toFormattedDate(new Date(modal.props.cachedFolder.series.date_updated)) }}
        </template>
        <EditFolder v-if="modal.props.cachedFolder" :folder="modal.props.cachedFolder" @handleFinish="handleSeriesUpdate" />
    </BaseModal>
</template>
