<script setup lang="ts">
import type { CategoryResource } from '@/types/resources';

import { computed, onMounted, ref, watch } from 'vue';
import { toast } from '@/service/toaster/toastService';

import CategoryCard from '@/components/cards/CategoryCard.vue';
import ButtonText from '@/components/inputs/ButtonText.vue';
import ModalBase from '@/components/pinesUI/ModalBase.vue';
import TableBase from '@/components/table/TableBase.vue';
import useModal from '@/composables/useModal';

import ProiconsArrowSync from '~icons/proicons/arrow-sync';
import ProiconsAdd from '~icons/proicons/add';

// const { data: rawCategories, isLoading } = useGetCategories();
const categories = ref<CategoryResource[]>([]);
const searchQuery = ref('');
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
]);

const filteredCategories = computed(() => {
    let tempList = searchQuery.value
        ? categories.value.filter((category: CategoryResource) => {
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
        : categories.value;
    return tempList;
});

const handleSort = async (column = 'date', dir = 1) => {
    let tempList = categories.value.sort((categoryA: CategoryResource, categoryB: CategoryResource) => {
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
    categories.value = tempList;
    return tempList;
};

const handleSearch = (query: string) => {
    searchQuery.value = query;
};

// watch(rawCategories, (v) => {
//     categories.value = v.data ?? [];
// });

// onMounted(() => {
//     if (rawCategories.value?.data) categories.value = rawCategories.value.data;
// });
</script>

<template>
    <section id="tasks" class="flex gap-8 flex-col">
        <div class="flex items-center gap-2 justify-between flex-wrap">
            <p class="uppercase">Running: {{ categories?.length }}</p>
            <div class="flex flex-wrap items-center gap-2 [&>*]:h-8">
                <ButtonText title="Start New Task" @click="toast.add('Success', { type: 'success', description: 'Submitted Scan Request!', life: 3000 })" disabled>
                    <template #text>New Task</template>
                    <template #icon><ProiconsAdd /></template>
                </ButtonText>
                <ButtonText @click="toast.add('Success', { type: 'success', description: 'Submitted File Indexing Request!', life: 3000 })" disabled>
                    <template #text>Run File Scan</template>
                    <template #icon><ProiconsArrowSync /></template>
                </ButtonText>
            </div>
        </div>
        <TableBase
            :use-grid="'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 2xl:grid-cols-4 3xl:grid-cols-5 gap-3'"
            :use-pagination="true"
            :data="[...filteredCategories]"
            :row="CategoryCard"
            :click-action="() => {}"
            :loading="false"
            :sort-action="handleSort"
            :sorting-options="sortingOptions"
            @search="handleSearch"
        />
    </section>
</template>
