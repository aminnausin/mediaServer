<script setup lang="ts">
import type { FolderResource } from '@/types/resources';
import type { SortDir } from '@/types/types';

import { formatFileSize, toTitleCase } from '@/service/util';
import { folderSortingOptions } from '@/constants/sortingOptions';
import { useContentStore } from '@/stores/ContentStore';
import { useModalStore } from '@/stores/ModalStore';
import { computed, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { ButtonIcon } from '@/components/cedar-ui/button';
import { sortObject } from '@/service/sort/baseSort';
import { TableBase } from '@/components/cedar-ui/table';
import { FLAGS } from '@/config/featureFlags';
import { cn } from '@aminnausin/cedar-ui';

import EditFolderModal from '@/components/modals/EditFolderModal.vue';
import SidebarHeader from '@/components/headers/SidebarHeader.vue';
import FolderCard from '@/components/cards/data/FolderCard.vue';
import ShareModal from '@/components/modals/ShareModal.vue';

import ProiconsFilterCancel from '~icons/proicons/filter-cancel';
import ProiconsFilter from '~icons/proicons/filter';

const stickyFilters = true;

const modal = useModalStore();

const folderSearchQuery = ref<string>('');
const folderSortDir = ref<SortDir>(1);
const folderSortKey = ref<keyof FolderResource>(folderSortingOptions[0].value);
const showFilters = ref(true);

const { stateDirectory, stateFolder } = storeToRefs(useContentStore());

const sortedFolders = computed<FolderResource[]>(() => {
    return [...stateDirectory.value.folders].sort(sortObject<FolderResource>(folderSortKey.value, folderSortDir.value, ['created_at', 'updated_at']));
});

const filteredFolders = computed<FolderResource[]>(() => {
    if (!folderSearchQuery.value) return sortedFolders.value;
    return sortedFolders.value.filter((folder) => {
        const tags = folder.series?.folder_tags?.map((tag) => tag.name) ?? [];
        const strRepresentation = [
            folder.title ?? folder.name,
            folder.id,
            folder.created_at,
            folder.updated_at,
            formatFileSize(folder.total_size),
            folder.file_count + (folder.is_majority_audio ? ' Tracks' : ' Episodes'),
            folder.series?.studio,
            ...tags,
        ]
            .join(' ')
            .toLowerCase();
        return strRepresentation.includes(folderSearchQuery.value.toLowerCase());
    });
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
    <span v-if="stickyFilters" :class="['bg-surface-1 absolute top-7.75 left-0 z-1 h-10.75 w-full shrink-0 lg:hidden', { 'h-32': showFilters }]"></span>
    <SidebarHeader :class="['gap-2', { 'sticky top-0 z-1 lg:static': stickyFilters }]" :text="stateDirectory.name" :title="toTitleCase(stateDirectory.name) + ' Folders'">
        <ButtonIcon
            v-if="FLAGS.USE_TOGGLE_FOLDER_FILTERS"
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
        v-model="folderSearchQuery"
        :data="filteredFolders"
        :row="FolderCard"
        :class="'full-height-sidebar [--table-input-height:2rem] lg:[--table-input-height:inherit]'"
        :otherAction="handleFolderAction"
        :useToolbar="showFilters"
        :startAscending="true"
        :row-attributes="{
            categoryName: stateDirectory.name,
            stateFolderName: stateFolder?.name,
        }"
        :items-per-page="10"
        :max-visible-pages="3"
        :table-styles="cn('gap-3 sm:gap-2')"
        :pagination-class="'justify-center! flex-col-reverse!'"
        :use-pagination-icons="true"
        :sort-action="
            (sortKey: keyof FolderResource, sortDir: SortDir) => {
                folderSortDir = sortDir;
                folderSortKey = sortKey;
            }
        "
        :sorting-options="folderSortingOptions"
        :force-vertical-toolbar="true"
        :sticky="stickyFilters"
        :sticky-class="'lg:static'"
    />
</template>
