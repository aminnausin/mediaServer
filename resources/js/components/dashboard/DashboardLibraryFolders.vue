<script setup lang="ts">
import type { FolderResource, SeriesResource } from '@/types/resources';

import { computed, onMounted, ref } from 'vue';
import { useDashboardStore } from '@/stores/DashboardStore';
import { useContentStore } from '@/stores/ContentStore';
import { useQueryClient } from '@tanstack/vue-query';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { sortObject } from '@/service/sort/baseSort';

import CategoryFolderCard from '@/components/cards/CategoryFolderCard.vue';
import EditFolder from '@/components/forms/EditFolder.vue';
import ModalBase from '@/components/pinesUI/ModalBase.vue';
import TableBase from '@/components/table/TableBase.vue';
import useModal from '@/composables/useModal';

const sortingOptions = ref([
    {
        title: 'ID',
        value: 'id',
    },
    {
        title: 'Title',
        value: 'name',
        disabled: false,
    },
    {
        title: 'Date',
        value: 'created_at',
        disabled: false,
    },
    {
        title: 'Videos',
        value: 'file_count',
        disabled: false,
    },
    {
        title: 'Total Size',
        value: 'total_size',
    },
]);

const { stateLibraryFolders, isLoadingLibraryFolders } = storeToRefs(useDashboardStore());
const { updateFolderData } = useContentStore();
const { pageTitle } = storeToRefs(useAppStore());

const editFolderModal = useModal({ title: 'Edit Folder Details', submitText: 'Submit Details' });
const queryClient = useQueryClient();
const cachedID = ref<null | number>(null);
const cachedFolder = ref<FolderResource>();
const searchQuery = ref('');

const filteredFolders = computed(() => {
    let tempList = searchQuery.value
        ? stateLibraryFolders.value.filter((folder: FolderResource) => {
              try {
                  let strRepresentation = [folder.name, folder.series?.title, folder.series?.description, folder.series?.studio, folder.created_at].join(' ').toLowerCase();
                  return strRepresentation.includes(searchQuery.value.toLowerCase());
              } catch (error) {
                  console.log(error);
                  return false;
              }
          })
        : stateLibraryFolders.value;
    return tempList;
});

const handleSort = async (column: keyof FolderResource = 'created_at', dir: -1 | 1 = 1) => {
    let tempList = [...stateLibraryFolders.value].sort(sortObject<FolderResource>(column, dir, ['created_at']));
    stateLibraryFolders.value = tempList;
    return tempList;
};

const handleFolderAction = (_: any, id: number, action: 'edit' | 'share' = 'edit') => {
    let folder = stateLibraryFolders.value?.find((folder: FolderResource) => folder.id === id);

    if (folder) cachedFolder.value = folder;

    if (action === 'edit') editFolderModal.toggleModal();
    // else shareFolderModal.toggleModal();
};

const handleSeriesUpdate = async (res: any) => {
    if (res?.data?.id) updateFolderData(res.data as SeriesResource);
    editFolderModal.toggleModal(false);

    await queryClient.invalidateQueries({
        queryKey: ['libraryFolders'],
    });
};

onMounted(() => {
    pageTitle.value = 'Library Folders';
});
</script>
<template>
    <TableBase
        :use-grid="'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 3xl:grid-cols-5 gap-3'"
        :use-pagination="true"
        :data="filteredFolders"
        :row="CategoryFolderCard"
        :loading="isLoadingLibraryFolders"
        :sort-action="handleSort"
        :click-action="handleFolderAction"
        :sorting-options="sortingOptions"
        @search="
            (query: string) => {
                searchQuery = query;
            }
        "
    />
    <ModalBase :modalData="editFolderModal" :useControls="false">
        <template #content>
            <div class="pt-2">
                <EditFolder v-if="cachedFolder" :folder="cachedFolder" @handleFinish="handleSeriesUpdate" />
            </div>
        </template>
    </ModalBase>
</template>
