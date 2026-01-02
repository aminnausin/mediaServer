<script setup lang="ts">
import type { GenericSortOption, SortDir } from '@/types/types';
import type { FolderResource } from '@/types/resources';

import { useContentStore } from '@/stores/ContentStore';
import { useModalStore } from '@/stores/ModalStore';
import { computed, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { ButtonIcon } from '@/components/cedar-ui/button';
import { sortObject } from '@/service/sort/baseSort';
import { TableBase } from '@/components/cedar-ui/table';

import EditFolderModal from '@/components/modals/EditFolderModal.vue';
import SidebarHeader from '@/components/headers/SidebarHeader.vue';
import FolderCard from '@/components/cards/data/FolderCard.vue';
import ShareModal from '@/components/modals/ShareModal.vue';

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

const modal = useModalStore();

const folderSortDir = ref<SortDir>(1);
const folderSortKey = ref<keyof FolderResource>(folderSortingOptions[0].value);
const showFilters = ref(false);

const { stateDirectory, stateFolder } = storeToRefs(useContentStore());

const sortedFolders = computed<FolderResource[]>(() => {
    return [...stateDirectory.value.folders].sort(sortObject<FolderResource>(folderSortKey.value, folderSortDir.value, ['created_at', 'updated_at']));
});

const handleFolderAction = (e: Event, id: number, action: 'edit' | 'share' = 'edit') => {
    const folder = stateDirectory.value?.folders?.find((folder: FolderResource) => folder.id === id);

    if (!folder?.id) return;

    if (action === 'edit') {
        modal.open(EditFolderModal, { cachedFolder: folder });
    } else {
        modal.open(ShareModal, { title: 'Share Folder', shareLink: encodeURI(globalThis.location.origin + '/' + folder.path) });
    }
};
</script>

<template>
    <SidebarHeader>
        <ButtonIcon
            v-if="stateDirectory.folders.length > 10"
            class="dark:hover:bg-primary-active size-8 p-0 *:size-6 dark:ring-transparent"
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
        :table-styles="'gap-3 sm:gap-2'"
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
</template>
