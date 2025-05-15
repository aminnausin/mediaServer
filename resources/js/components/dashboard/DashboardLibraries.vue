<script setup lang="ts">
import type { CategoryResource } from '@/types/resources';

import { computed, onMounted, ref } from 'vue';
import { startIndexFilesTask } from '@/service/siteAPI';
import { useDashboardStore } from '@/stores/DashboardStore';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { sortObject } from '@/service/util';
import { toast } from '@/service/toaster/toastService';

import DashboardLibraryFolders from '@/components/dashboard/DashboardLibraryFolders.vue';
import CategoryCard from '@/components/cards/CategoryCard.vue';
import ButtonText from '@/components/inputs/ButtonText.vue';
import ModalBase from '@/components/pinesUI/ModalBase.vue';
import TableBase from '@/components/table/TableBase.vue';
import useModal from '@/composables/useModal';

import ProiconsSearch from '~icons/proicons/search';
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

const { stateLibraries, isLoadingLibraries, stateLibraryId } = storeToRefs(useDashboardStore());
const { pageTitle } = storeToRefs(useAppStore());

const cachedID = ref<null | number>(null);
const searchQuery = ref('');
const confirmModal = useModal({ title: 'Delete Category?', submitText: 'Confim' });

const filteredCategories = computed(() => {
    let tempList = searchQuery.value
        ? stateLibraries.value.filter((category: CategoryResource) => {
              try {
                  let strRepresentation = [category.name, category.folders_count, category.folders[0]?.name ?? '', category.created_at].join(' ').toLowerCase();
                  return strRepresentation.includes(searchQuery.value.toLowerCase());
              } catch (error) {
                  console.log(error);
                  return false;
              }
          })
        : stateLibraries.value;
    return tempList;
});

const handleDelete = (id: number) => {
    cachedID.value = id;
    confirmModal.toggleModal(true);
};

const submitDelete = async () => {};

const handleSort = async (column: keyof CategoryResource = 'created_at', dir: -1 | 1 = 1) => {
    let tempList = [...stateLibraries.value].sort(sortObject<CategoryResource>(column, dir, ['created_at']));
    stateLibraries.value = tempList;
    return tempList;
};

const handleStartScan = async () => {
    try {
        await startIndexFilesTask();

        toast.add('Success', { type: 'success', description: `Submitted scan Request!` });
    } catch (error) {
        toast('Failure', { type: 'danger', description: `Unable to submit scan request.` });
        console.error(error);
    }
};

onMounted(() => {
    pageTitle.value = 'Libraries';
});
</script>
<template>
    <DashboardLibraryFolders v-if="stateLibraryId > 0" />
    <section v-else id="content-libraries" class="flex gap-8 flex-col">
        <div class="flex items-center gap-2 justify-between flex-wrap">
            <div class="flex flex-wrap items-center gap-2 [&>*]:h-fit [&>*]:xs:h-8">
                <ButtonText title="Add New Library" disabled>
                    <template #text>New Library</template>
                    <template #icon><ProiconsAdd /></template>
                </ButtonText>
                <ButtonText @click="handleStartScan" :title="'Index Files'">
                    <template #text>Look For Changes</template>
                    <template #icon><ProiconsSearch /></template>
                </ButtonText>
            </div>
            <p class="capitalize text-sm font-medium">Count: {{ stateLibraries?.length }}</p>
        </div>
        <TableBase
            :use-grid="'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 3xl:grid-cols-5 gap-3'"
            :use-pagination="true"
            :data="filteredCategories"
            :row="CategoryCard"
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
</template>
