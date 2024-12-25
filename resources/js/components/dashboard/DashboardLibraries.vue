<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { getCategories } from '@/service/mediaAPI';

import RecordCardDetails from '@/components/cards/RecordCardDetails.vue';
import ButtonText from '@/components/inputs/ButtonText.vue';
import ButtonIcon from '@/components/inputs/ButtonIcon.vue';
import ModalBase from '@/components/pinesUI/ModalBase.vue';
import TableBase from '@/components/table/TableBase.vue';
import useModal from '@/composables/useModal';

const loading = ref(false);
const cachedID = ref<null | number>(null);
const confirmModal = useModal({ title: 'Delete Category?', submitText: 'Confim' });
const searchQuery = ref('');

import type { CategoryResource } from '@/types/resources';

import ProiconsAddCircle from '~icons/proicons/add-circle';
import CategoryCard from '../cards/CategoryCard.vue';

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
    let tempList = categories.value.sort((categoryA, categoryB) => {
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

onMounted(async () => {
    const { data: rawCategories } = await getCategories();

    categories.value = rawCategories?.data;
});
</script>
<template>
    <section id="content-libraries" class="flex gap-2 flex-col">
        <span class="flex items-center justify-between">
            <ButtonText title="Add New Category">
                <template #text>
                    <div class="flex justify-between items-center gap-2">
                        <p class="text-nowrap">Add New Category</p>
                        <ProiconsAddCircle height="24" width="24" />
                    </div>
                </template>
            </ButtonText>
            <p class="text-slate-400 uppercase">Categories: {{ categories.length }}</p>
        </span>
        <TableBase
            :data="filteredCategories"
            :row="CategoryCard"
            :clickAction="handleDelete"
            :loading="loading"
            :useToolbar="true"
            :sortAction="handleSort"
            :sortingOptions="sortingOptions"
            @search="handleSearch"
        />
    </section>
    <ModalBase :modalData="confirmModal" :action="submitDelete">
        <template #content>
            <div class="relative w-auto pb-8">
                <p>Are you sure you want to delete this record?</p>
            </div>
        </template>
    </ModalBase>
</template>
