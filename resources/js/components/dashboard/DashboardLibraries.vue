<script setup lang="ts">
import type { CategoryResource, FolderResource } from '@/types/resources';
import type { BreadCrumbItem } from '@/types/types';

import { computed, ref, watchEffect } from 'vue';
import { startScanFilesTask } from '@/service/siteAPI';
import { useDashboardStore } from '@/stores/DashboardStore';
import { useModalStore } from '@/stores/ModalStore';
import { BreadCrumbs } from '@/components/cedar-ui/breadcrumbs';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { sortObject } from '@/service/sort/baseSort';
import { ButtonText } from '@/components/cedar-ui/button';
import { TableBase } from '@/components/cedar-ui/table';
import { ModalBase } from '@/components/cedar-ui/modal';
import { toast } from '@aminnausin/cedar-ui';

import LibraryFolderCard from '@/components/cards/data/LibraryFolderCard.vue';
import EditFolderModal from '@/components/modals/EditFolderModal.vue';
import LibraryCard from '@/components/cards/data/LibraryCard.vue';
import useModal from '@/composables/useModal';

import ProiconsLibrary from '~icons/proicons/library';
import ProiconsSearch from '~icons/proicons/search';
import ProiconsHome2 from '~icons/proicons/home-2';
import ProiconsAdd from '~icons/proicons/add';

const sortingOptions = ref([
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
        title: 'Folders',
        value: 'folders_count',
        disabled: false,
    },
    {
        title: 'Videos',
        value: 'videos_count',
        disabled: false,
    },
    {
        title: 'Size',
        value: 'total_size',
        disabled: false,
    },
]);

const folderSortingOptions = ref([
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

const { stateLibraries, isLoadingLibraries, stateLibraryId, stateLibraryFolders, isLoadingLibraryFolders } = storeToRefs(useDashboardStore());
const { pageTitle } = storeToRefs(useAppStore());

const confirmModal = useModal({ title: 'Delete Library?', submitText: 'Confim' });
const cachedLibrary = ref<CategoryResource>();
const searchQuery = ref('');
const cachedID = ref<null | number>(null);

const modal = useModalStore();

const breadCrumbs = computed(() => {
    const items: BreadCrumbItem[] = [
        {
            name: 'Dashboard',
            url: '/dashboard/analytics',
            icon: ProiconsHome2,
        },
        {
            name: 'Libraries',
            url: '/dashboard/libraries',
            icon: ProiconsLibrary,
        },
    ];

    if (cachedLibrary.value)
        return [
            ...items,
            {
                name: cachedLibrary.value.name,
                url: `/dashboard/libraries/${cachedLibrary.value.id}`,
            },
        ];
    return items;
});

const filteredLibraries = computed(() => {
    const tempList = searchQuery.value
        ? stateLibraries.value.filter((category: CategoryResource) => {
              try {
                  const strRepresentation = [
                      category.name,
                      category.folders_count,
                      category.folders.find((folder) => folder.id === category.default_folder_id)?.name ?? '',
                      category.created_at,
                  ]
                      .join(' ')
                      .toLowerCase();

                  return strRepresentation.includes(searchQuery.value.toLowerCase());
              } catch (error) {
                  console.log(error);
                  return false;
              }
          })
        : stateLibraries.value;
    return tempList;
});

const filteredFolders = computed(() => {
    const tempList = searchQuery.value
        ? stateLibraryFolders.value.filter((folder: FolderResource) => {
              try {
                  const strRepresentation = [folder.title, folder.series?.title, folder.series?.description, folder.series?.studio, folder.created_at].join(' ').toLowerCase();
                  return strRepresentation.includes(searchQuery.value.toLowerCase());
              } catch (error) {
                  console.log(error);
                  return false;
              }
          })
        : stateLibraryFolders.value;
    return tempList;
});

const gridCols = computed(() => {
    const maxCols = (stateLibraryId.value !== -1 ? filteredFolders.value.length : filteredLibraries.value.length) || 5;

    return `grid grid-cols-1 sm:grid-cols-${Math.min(2, maxCols)} md:grid-cols-${Math.min(3, maxCols)} lg:grid-cols-${Math.min(2, maxCols)} xl:grid-cols-${Math.min(3, maxCols)} 2xl:grid-cols-${Math.min(4, maxCols)} 3xl:grid-cols-${Math.min(5, maxCols)} gap-3`;
});

const handleDelete = (id: number) => {
    //Unimplemented
    cachedID.value = id;
    confirmModal.toggleModal(true);
};

const submitDelete = async () => {};

const handleSort = async (column: keyof CategoryResource = 'created_at', dir: -1 | 1 = 1) => {
    const tempList = [...stateLibraries.value].sort(sortObject<CategoryResource>(column, dir, ['created_at']));
    stateLibraries.value = tempList;
    return tempList;
};

const handleFolderSort = async (column: keyof FolderResource = 'created_at', dir: -1 | 1 = 1) => {
    const tempList = [...stateLibraryFolders.value].sort(sortObject<FolderResource>(column, dir, ['created_at']));
    stateLibraryFolders.value = tempList;
    return tempList;
};

const handleStartScan = async () => {
    try {
        await startScanFilesTask(stateLibraryId.value > 0 ? stateLibraryId.value : undefined);

        const scanMessage = 'Submitted scan request' + (cachedLibrary.value?.name ? ` for ${cachedLibrary.value.name}!` : '!');

        toast.add('Success', { type: 'success', description: scanMessage });
    } catch (error) {
        toast('Failure', { type: 'danger', description: `Unable to submit scan request.` });
        console.error(error);
    }
};

const handleFolderAction = (_: any, id: number, action: 'edit' | 'share' = 'edit') => {
    const folder = stateLibraryFolders.value?.find((folder: FolderResource) => folder.id === id);

    if (action === 'edit') modal.open(EditFolderModal, { cachedFolder: folder, queryKeys: [['libraryFolders']] });
};

watchEffect(() => {
    cachedLibrary.value = stateLibraries.value.find((library: CategoryResource) => library.id == stateLibraryId.value);

    const title = cachedLibrary.value ? `Content Libraries · ${cachedLibrary.value.name}` : 'Content Libraries';
    pageTitle.value = title;
    document.title = title;
    searchQuery.value = '';
});
</script>
<template>
    <div class="flex flex-wrap items-center justify-between gap-2">
        <BreadCrumbs :bread-crumbs="breadCrumbs" />

        <p class="font-medium capitalize" v-if="stateLibraryId < 0">Count: {{ stateLibraries?.length }}</p>
        <span v-else class="flex gap-2 overflow-clip">
            <p class="font-medium capitalize">Count: {{ stateLibraryFolders?.length }}</p>
            <p class="font-medium capitalize">Videos: {{ stateLibraryFolders?.reduce((total: number, folder: FolderResource) => total + Number(folder.file_count), 0) }}</p>
        </span>
        <div class="xs:*:h-8 flex w-full flex-wrap items-center gap-2 *:h-fit">
            <ButtonText title="Add New Library" disabled text="New Library" class="xs:flex-initial hidden flex-1">
                <template #icon><ProiconsAdd /></template>
            </ButtonText>
            <ButtonText @click="handleStartScan" title="Index Files" text="Scan For Changes" class="xs:flex-initial flex-1">
                <template #icon><ProiconsSearch /></template>
            </ButtonText>
        </div>
    </div>
    <TableBase
        v-if="stateLibraryId > 0"
        :use-grid="gridCols"
        :use-pagination="true"
        :data="filteredFolders"
        :row="LibraryFolderCard"
        :loading="isLoadingLibraryFolders"
        :sort-action="handleFolderSort"
        :click-action="handleFolderAction"
        :sorting-options="folderSortingOptions"
        v-model="searchQuery"
    />

    <TableBase
        v-else
        :use-grid="gridCols"
        :use-pagination="true"
        :data="filteredLibraries"
        :row="LibraryCard"
        :click-action="handleDelete"
        :loading="isLoadingLibraries"
        :sort-action="handleSort"
        :sorting-options="sortingOptions"
        v-model="searchQuery"
    />
    <ModalBase :modalData="confirmModal" :action="submitDelete">
        <template #description> Are you sure you want to delete this Library? </template>
    </ModalBase>
</template>
