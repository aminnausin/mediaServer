<script setup lang="ts">
// Unimplemented?
import type { CategoryResource } from '@/types/resources';

import { computed, ref } from 'vue';
import { sortObject } from '@/service/sort/baseSort';
import { toast } from '@/service/toaster/toastService';

import LibraryCard from '@/components/cards/LibraryCard.vue';
import ButtonText from '@/components/inputs/ButtonText.vue';
import TableBase from '@/components/table/TableBase.vue';

import ProiconsArrowSync from '~icons/proicons/arrow-sync';
import ProiconsAdd from '~icons/proicons/add';

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
    const tempList = searchQuery.value
        ? categories.value.filter((category: CategoryResource) => {
              try {
                  const strRepresentation = [category.name, category.folders_count, category.folders[0]?.name ?? '', category.created_at].join(' ').toLowerCase();
                  return strRepresentation.includes(searchQuery.value.toLowerCase());
              } catch (error) {
                  console.log(error);
                  return false;
              }
          })
        : categories.value;
    return tempList;
});

const handleSort = async (column: keyof CategoryResource = 'created_at', dir: -1 | 1 = 1) => {
    const tempList = [...categories.value].sort(sortObject<CategoryResource>(column, dir, ['created_at']));
    categories.value = tempList;
    return tempList;
};

const handleSearch = (query: string) => {
    searchQuery.value = query;
};
</script>

<template>
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
        <span class="hidden lg:col-span-3"></span>
    </div>
    <TableBase
        :use-grid="'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 2xl:grid-cols-4 3xl:grid-cols-5 gap-3'"
        :use-pagination="true"
        :data="[...filteredCategories]"
        :row="LibraryCard"
        :click-action="() => {}"
        :loading="false"
        :sort-action="handleSort"
        :sorting-options="sortingOptions"
        @search="handleSearch"
    />
</template>
