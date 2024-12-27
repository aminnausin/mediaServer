<script setup lang="ts">
import type { CategoryResource } from '@/types/resources';

import { computed, onMounted, ref, watch } from 'vue';
import { useGetCategories } from '@/service/queries';
import { toast } from '@/service/toaster/toastService';

import CategoryCard from '@/components/cards/CategoryCard.vue';
import ButtonText from '@/components/inputs/ButtonText.vue';
import ModalBase from '@/components/pinesUI/ModalBase.vue';
import TableBase from '@/components/table/TableBase.vue';
import useModal from '@/composables/useModal';

import ProiconsArrowSync from '~icons/proicons/arrow-sync';
import ProiconsAdd from '~icons/proicons/add';

const cachedID = ref<null | number>(null);
const confirmModal = useModal({ title: 'Delete Category?', submitText: 'Confim' });
const searchQuery = ref('');

const { data: rawCategories, isLoading } = useGetCategories();
const categories = ref<CategoryResource[]>([]);

const filteredCategories = computed(() => {
    let tempList = searchQuery.value
        ? categories.value.filter((category: CategoryResource) => {
              {
                  try {
                      let strRepresentation = [category.name, category.folders_count, category.folders[0]?.name ?? '', category.created_at]
                          .join(' ')
                          .toLowerCase();
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

const handleDelete = (id: number) => {
    cachedID.value = id;
    confirmModal.toggleModal(true);
};

const submitDelete = async () => {
    if (cachedID.value) {
        // let request = await deleteRecord(cachedID.value);
        // if (request) toast.add({ type: 'success', title: 'Success', description: 'Record Deleted Successfully!', life: 3000 });
        // else toast.add({ type: 'warning', title: 'Error', description: 'Unable to delete record. Please try again.', life: 3000 });
    }
};

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

const handleSort = async (column = 'date', dir = 1) => {
    let tempList = categories.value.sort((categoryA: CategoryResource, categoryB: CategoryResource) => {
        if (column === 'created_at') {
            let dateA = new Date(categoryA?.created_at ?? '');
            let dateB = new Date(categoryB?.created_at ?? '');
            return (dateB.getTime() - dateA.getTime()) * dir;
        }
        return `${categoryB[column as keyof CategoryResource]}`?.localeCompare(`${categoryA[column as keyof CategoryResource]}`) * dir;
    });
    categories.value = tempList;
    return tempList;
};

const handleSearch = (query: string) => {
    searchQuery.value = query;
};

watch(rawCategories, (v) => {
    categories.value = v.data ?? [];
});

onMounted(() => {
    if (rawCategories.value?.data) categories.value = rawCategories.value.data;
});
</script>
<template>
    <section id="content-libraries" class="flex gap-8 flex-col">
        <div class="flex items-center gap-2 justify-between flex-wrap">
            <p class="uppercase">Installed: {{ categories?.length }}</p>
            <div class="flex flex-wrap items-center gap-2 [&>*]:h-8">
                <ButtonText
                    title="Add New Library"
                    @click="toast.add('Success', { type: 'success', description: 'Submitted Scan Request!', life: 3000 })"
                    disabled
                >
                    <template #text>New Library</template>
                    <template #icon><ProiconsAdd /></template>
                </ButtonText>
                <ButtonText @click="toast.add('Success', { type: 'success', description: 'Submitted Scan Request!', life: 3000 })" disabled>
                    <template #text>Run Full Scan</template>
                    <template #icon><ProiconsArrowSync /></template>
                </ButtonText>
            </div>
        </div>
        <TableBase
            :use-grid="'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 2xl:grid-cols-4 3xl:grid-cols-5 gap-3'"
            :use-pagination="true"
            :data="[...filteredCategories]"
            :row="CategoryCard"
            :click-action="handleDelete"
            :loading="isLoading"
            :sort-action="handleSort"
            :sorting-options="sortingOptions"
            @search="handleSearch"
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
