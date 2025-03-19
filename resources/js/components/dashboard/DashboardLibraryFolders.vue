<script setup lang="ts">
import type { FolderResource, SeriesResource } from '@/types/resources';

import { computed, onMounted, ref } from 'vue';
import { startIndexFilesTask } from '@/service/siteAPI';
import { useDashboardStore } from '@/stores/DashboardStore';
import { useContentStore } from '@/stores/ContentStore';
import { useQueryClient } from '@tanstack/vue-query';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { toast } from '@/service/toaster/toastService';

import CategoryFolderCard from '@/components/cards/CategoryFolderCard.vue';
import ButtonText from '@/components/inputs/ButtonText.vue';
import EditFolder from '@/components/forms/EditFolder.vue';
import ModalBase from '@/components/pinesUI/ModalBase.vue';
import TableBase from '@/components/table/TableBase.vue';
import useModal from '@/composables/useModal';

import ProiconsArrowSync from '~icons/proicons/arrow-sync';

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

const handleSort = async (column = 'name', dir = 1) => {
    let tempList = [...stateLibraryFolders.value];
    tempList.sort((folderA: FolderResource, folderB: FolderResource) => {
        if (column === 'created_at') {
            let dateA = new Date(folderA?.created_at ?? '');
            let dateB = new Date(folderB?.created_at ?? '');
            return (dateB.getTime() - dateA.getTime()) * dir;
        }
        if (column === 'name' && folderA.series?.title && folderB.series?.title) {
            return `${folderA.series?.title}`?.localeCompare(`${folderB.series?.title}`) * dir;
        }
        let valueA = folderA[column as keyof FolderResource];
        let valueB = folderB[column as keyof FolderResource];
        if (valueA && valueB && typeof valueA === 'number' && typeof valueB === 'number') return (valueA - valueB) * dir;
        return `${valueA}`?.localeCompare(`${valueB}`) * dir;
    });
    stateLibraryFolders.value = tempList;
    return tempList;
};

const handleStartScan = async () => {
    try {
        await startIndexFilesTask();

        toast.add('Success', { type: 'success', description: `Submitted scan Request!` });
    } catch (error) {
        toast('Failure', { type: 'danger', description: `Unable to submit scan request.` });
    }
};

const handleFolderAction = (e: Event, id: number, action: 'edit' | 'share' = 'edit') => {
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
    <section id="content-libraries" class="flex gap-8 flex-col">
        <div class="flex items-center gap-2 justify-between flex-wrap">
            <div class="flex flex-wrap items-center gap-2 [&>*]:h-fit [&>*]:xs:h-8">
                <ButtonText title="Return to Libraries" to="/dashboard/libraries" target="">
                    <template #text>Return to Libraries</template>
                </ButtonText>
                <ButtonText @click="handleStartScan">
                    <template #text>Scan For Changes</template>
                    <template #icon>
                        <ProiconsArrowSync />
                    </template>
                </ButtonText>
            </div>
            <span>
                <p class="capitalize text-sm font-medium">Folders: {{ stateLibraryFolders?.length }}</p>
                <p class="capitalize text-sm font-medium">
                    Videos: {{ stateLibraryFolders?.reduce((total: number, folder: FolderResource) => total + Number(folder.file_count), 0) }}
                </p>
            </span>
        </div>
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
    </section>
    <ModalBase :modalData="editFolderModal" :useControls="false">
        <template #content>
            <div class="pt-2">
                <EditFolder :folder="cachedFolder" @handleFinish="handleSeriesUpdate" />
            </div>
        </template>
    </ModalBase>
</template>
