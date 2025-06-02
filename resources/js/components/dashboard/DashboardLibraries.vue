<script setup lang="ts">
import type { CategoryResource, FolderResource, SeriesResource } from '@/types/resources';
import type { BreadCrumbItem } from '@/types/types';

import { computed, onMounted, ref, watchEffect } from 'vue';
import { startScanFilesTask } from '@/service/siteAPI';
import { useDashboardStore } from '@/stores/DashboardStore';
import { useContentStore } from '@/stores/ContentStore';
import { useQueryClient } from '@tanstack/vue-query';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { sortObject } from '@/service/sort/baseSort';
import { toast } from '@/service/toaster/toastService';

import LibraryFolderCard from '@/components/cards/LibraryFolderCard.vue';
import LibraryCard from '@/components/cards/LibraryCard.vue';
import BreadCrumbs from '@/components/pinesUI/BreadCrumbs.vue';
import ButtonText from '@/components/inputs/ButtonText.vue';
import EditFolder from '@/components/forms/EditFolder.vue';
import ModalBase from '@/components/pinesUI/ModalBase.vue';
import TableBase from '@/components/table/TableBase.vue';
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
const { updateFolderData } = useContentStore();
const { pageTitle } = storeToRefs(useAppStore());

const editFolderModal = useModal({ title: 'Edit Folder Details', submitText: 'Submit Details' });
const confirmModal = useModal({ title: 'Delete Library?', submitText: 'Confim' });
const cachedLibrary = ref<CategoryResource>();
const cachedFolder = ref<FolderResource>();
const searchQuery = ref('');
const cachedID = ref<null | number>(null);
const queryClient = useQueryClient();

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
                  const strRepresentation = [folder.name, folder.series?.title, folder.series?.description, folder.series?.studio, folder.created_at].join(' ').toLowerCase();
                  return strRepresentation.includes(searchQuery.value.toLowerCase());
              } catch (error) {
                  console.log(error);
                  return false;
              }
          })
        : stateLibraryFolders.value;
    return tempList;
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

    if (folder) cachedFolder.value = folder;

    if (action === 'edit') editFolderModal.toggleModal();
};

const handleSeriesUpdate = async (res: any) => {
    if (res?.data?.id) updateFolderData(res.data as SeriesResource);
    editFolderModal.toggleModal(false);

    await queryClient.invalidateQueries({
        queryKey: ['libraryFolders'],
    });
};

watchEffect(() => {
    cachedLibrary.value = stateLibraries.value.find((library: CategoryResource) => library.id == stateLibraryId.value);

    const title = cachedLibrary.value ? `Content Libraries · ${cachedLibrary.value.name}` : 'Content Libraries';
    pageTitle.value = title;
    document.title = title;
});

onMounted(() => {});
</script>
<template>
    <section id="content-libraries" class="flex gap-4 flex-col">
        <div class="flex items-center gap-2 justify-between flex-wrap">
            <BreadCrumbs :bread-crumbs="breadCrumbs" />

            <p class="capitalize font-medium" v-if="stateLibraryId < 0">Count: {{ stateLibraries?.length }}</p>
            <span v-else class="flex overflow-clip gap-2">
                <p class="capitalize font-medium">Count: {{ stateLibraryFolders?.length }}</p>
                <p class="capitalize font-medium">Videos: {{ stateLibraryFolders?.reduce((total: number, folder: FolderResource) => total + Number(folder.file_count), 0) }}</p>
            </span>
            <div class="flex flex-wrap items-center gap-2 [&>*]:h-fit [&>*]:xs:h-8 w-full">
                <ButtonText title="Add New Library" disabled class="hidden">
                    <template #text>New Library</template>
                    <template #icon><ProiconsAdd /></template>
                </ButtonText>
                <ButtonText @click="handleStartScan" :title="'Index Files'">
                    <template #text>Scan For Changes</template>
                    <template #icon><ProiconsSearch /></template>
                </ButtonText>
            </div>
        </div>
        <TableBase
            v-if="stateLibraryId > 0"
            :use-grid="'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 3xl:grid-cols-5 gap-3'"
            :use-pagination="true"
            :data="filteredFolders"
            :row="LibraryFolderCard"
            :loading="isLoadingLibraryFolders"
            :sort-action="handleFolderSort"
            :click-action="handleFolderAction"
            :sorting-options="folderSortingOptions"
            @search="
                (query: string) => {
                    searchQuery = query;
                }
            "
        />

        <TableBase
            v-else
            :use-grid="'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 3xl:grid-cols-5 gap-3'"
            :use-pagination="true"
            :data="filteredLibraries"
            :row="LibraryCard"
            :click-action="handleDelete"
            :loading="isLoadingLibraries"
            :sort-action="handleSort"
            :sorting-options="sortingOptions"
            @search="
                (query: string) => {
                    searchQuery = query;
                }
            "
        />
    </section>
    <ModalBase :modalData="confirmModal" :action="submitDelete">
        <template #content>
            <div class="relative w-auto pb-8">
                <p>Are you sure you want to delete this Library?</p>
            </div>
        </template>
    </ModalBase>
    <ModalBase :modalData="editFolderModal" :useControls="false">
        <template #content>
            <div class="pt-2">
                <EditFolder v-if="cachedFolder" :folder="cachedFolder" @handleFinish="handleSeriesUpdate" />
            </div>
        </template>
    </ModalBase>
</template>
