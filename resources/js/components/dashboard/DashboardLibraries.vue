<script setup lang="ts">
import type { CategoryResource } from '@/types/resources';

import { computed, onMounted, ref, watch, type Ref } from 'vue';
import { startIndexFilesTask } from '@/service/siteAPI';
import { useDashboardStore } from '@/stores/DashboardStore';
import { useGetCategories } from '@/service/queries';
import { storeToRefs } from 'pinia';
import { toast } from '@/service/toaster/toastService';

import CategoryCard from '@/components/cards/CategoryCard.vue';
import ButtonText from '@/components/inputs/ButtonText.vue';
import ModalBase from '@/components/pinesUI/ModalBase.vue';
import TableBase from '@/components/table/TableBase.vue';
import useModal from '@/composables/useModal';

import ProiconsArrowSync from '~icons/proicons/arrow-sync';
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
]);

const { stateLibraries, isLoadingLibraries } = storeToRefs(useDashboardStore());

const cachedID = ref<null | number>(null);
const searchQuery = ref('');
const confirmModal = useModal({ title: 'Delete Category?', submitText: 'Confim' });

const filteredCategories = computed(() => {
    let tempList = searchQuery.value
        ? stateLibraries.value.filter((category: CategoryResource) => {
              {
                  try {
                      let strRepresentation = [category.name, category.folders_count, category.folders[0]?.name ?? '', category.created_at].join(' ').toLowerCase();
                      return strRepresentation.includes(searchQuery.value.toLowerCase());
                  } catch (error) {
                      console.log(error);
                      return false;
                  }
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

const handleSort = async (column = 'name', dir = 1) => {
    let tempList = [...stateLibraries.value];
    tempList.sort((categoryA: CategoryResource, categoryB: CategoryResource) => {
        if (column === 'created_at') {
            let dateA = new Date(categoryA?.created_at ?? '');
            let dateB = new Date(categoryB?.created_at ?? '');
            return (dateB.getTime() - dateA.getTime()) * dir;
        }
        let valueA = categoryA[column as keyof CategoryResource];
        let valueB = categoryB[column as keyof CategoryResource];
        if (valueA && valueB && typeof valueA === 'number' && typeof valueB === 'number') return (valueA - valueB) * dir;
        return `${valueA}`?.localeCompare(`${valueB}`) * dir;
    });
    stateLibraries.value = tempList;
    return tempList;
};

const handleStartScan = async () => {
    try {
        const result = await startIndexFilesTask();

        toast.add('Success', { type: 'success', description: `Submitted scan Request!` });
    } catch (error) {
        toast('Failure', { type: 'danger', description: `Unable to submit scan request.` });
    }
};

// onMounted(() => {
//     if (rawCategories.value?.data) stateLibraries.value = rawCategories.value.data;
// });
</script>
<template>
    <section id="content-libraries" class="flex gap-8 flex-col">
        <div class="flex items-center gap-2 justify-between flex-wrap">
            <div class="flex flex-wrap items-center gap-2 [&>*]:h-fit [&>*]:xs:h-8">
                <ButtonText title="Add New Library" @click="toast.add('Success', { type: 'success', description: 'Submitted Scan Request!', life: 3000 })" disabled>
                    <template #text>New Library</template>
                    <template #icon><ProiconsAdd /></template>
                </ButtonText>
                <ButtonText @click="handleStartScan">
                    <template #text>Scan For Changes</template>
                    <template #icon><ProiconsArrowSync /></template>
                </ButtonText>
            </div>
            <p class="capitalize text-sm font-medium">Count: {{ stateLibraries?.length }}</p>
        </div>
        <TableBase
            :use-grid="'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 2xl:grid-cols-4 3xl:grid-cols-5 gap-3'"
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
