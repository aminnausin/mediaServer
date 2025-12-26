<script setup lang="ts">
import type { FolderResource, SeriesResource } from '@/types/resources';
import type { GenericSortOption, SortDir } from '@/types/types';

import { useContentStore } from '@/stores/ContentStore';
import { toFormattedDate } from '@/service/util';
import { computed, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { sortObject } from '@/service/sort/baseSort';
import { TableBase } from '@/components/cedar-ui/table';

import ButtonClipboard from '@/components/pinesUI/ButtonClipboard.vue';

import SidebarHeader from '@/components/headers/SidebarHeader.vue';
import ButtonIcon from '@/components/inputs/ButtonIcon.vue';
import FolderCard from '@/components/cards/sidebar/FolderCard.vue';
import EditFolder from '@/components/forms/EditFolder.vue';
import ModalBase from '@/components/pinesUI/ModalBase.vue';
import useModal from '@/composables/useModal';

import ProiconsFilterCancel from '~icons/proicons/filter-cancel';
import ProiconsFilter from '~icons/proicons/filter';

const folderSortingOptions: GenericSortOption<FolderResource>[] = [
    {
        title: 'Title',
        value: 'name',
    },
    {
        title: 'Date Created',
        value: 'created_at',
    },
    {
        title: 'Date Updated',
        value: 'updated_at',
    },
    {
        title: 'Size',
        value: 'total_size',
    },
    {
        title: 'File Count',
        value: 'file_count',
    },
];

const editFolderModal = useModal({ title: 'Edit Folder Details', submitText: 'Submit Details' });
const shareModal = useModal({ title: 'Share Video' });
const cachedFolder = ref<FolderResource>();
const shareLink = ref('');

const folderSortDir = ref<SortDir>(1);
const folderSortKey = ref<keyof FolderResource>(folderSortingOptions[0].value);
const showFilters = ref(false);

const { stateDirectory, stateFolder } = storeToRefs(useContentStore());

const { updateFolderData } = useContentStore();

const sortedFolders = computed<FolderResource[]>(() => {
    return [...stateDirectory.value.folders].sort(sortObject<FolderResource>(folderSortKey.value, folderSortDir.value, ['created_at', 'updated_at']));
});

const handleFolderAction = (e: Event, id: number, action: 'edit' | 'share' = 'edit') => {
    const folder = stateDirectory.value?.folders?.find((folder: FolderResource) => folder.id === id);
    if (!folder?.id) return;

    cachedFolder.value = folder;
    if (action === 'edit') editFolderModal.toggleModal();
    else {
        shareLink.value = encodeURI(window.location.origin + '/' + folder.path);
        shareModal.toggleModal(true);
    }
};

const handleSeriesUpdate = async (res: any) => {
    if (res?.data?.id) updateFolderData(res.data as SeriesResource);
    editFolderModal.toggleModal(false);
};
</script>

<template>
    <SidebarHeader>
        <ButtonIcon
            v-if="stateDirectory.folders.length > 10"
            class="size-8! p-0! *:size-6 dark:ring-transparent! dark:hover:bg-violet-700!"
            @click="showFilters = !showFilters"
            title="Toggle Filters"
        >
            <template #icon>
                <component :is="showFilters ? ProiconsFilterCancel : ProiconsFilter" />
            </template>
        </ButtonIcon>
    </SidebarHeader>
    <TableBase
        id="list-content-folders"
        :data="sortedFolders"
        :row="FolderCard"
        :otherAction="handleFolderAction"
        :useToolbar="stateDirectory.folders.length > 10 && showFilters"
        :startAscending="true"
        :row-attributes="{
            categoryName: stateDirectory.name,
            stateFolderName: stateFolder?.name,
        }"
        :items-per-page="10"
        :max-visible-pages="3"
        :pagination-class="'justify-center! flex-col-reverse!'"
        :use-pagination-icons="true"
        :sort-action="
            (sortKey: keyof FolderResource, sortDir: SortDir) => {
                folderSortDir = sortDir;
                folderSortKey = sortKey;
            }
        "
        :sorting-options="folderSortingOptions"
    />

    <ModalBase :modalData="shareModal">
        <template #description> Copy link to clipboard to share it.</template>
        <template #controls>
            <ButtonClipboard :text="shareLink" />
        </template>
    </ModalBase>
    <ModalBase :modalData="editFolderModal" :useControls="false">
        <template #description v-if="cachedFolder && cachedFolder.series?.editor_id && cachedFolder.series.date_updated">
            Last edited by
            <a title="Editor profile" target="_blank" :href="`/profile/${cachedFolder.series.editor_id}`" class="hover:text-purple-600 dark:hover:text-purple-500"
                >@{{ cachedFolder.series.editor_id }}</a
            >
            at
            {{ toFormattedDate(new Date(cachedFolder.series.date_updated)) }}
        </template>
        <template #content>
            <EditFolder v-if="cachedFolder" :folder="cachedFolder" @handleFinish="handleSeriesUpdate" />
        </template>
    </ModalBase>
</template>
