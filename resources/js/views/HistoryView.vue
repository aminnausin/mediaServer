<script setup lang="ts">
import type { GenericSortOption } from '@/types/types';
import type { RecordResource } from '@/types/resources';
import type { SortDir } from '@aminnausin/cedar-ui';

import { computed, onMounted, ref } from 'vue';
import { useRecord, useRecords } from '@/service/records/useRecords';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { sortObject } from '@/service/sort/baseSort';
import { toast } from '@/service/toaster/toastService';

import RecordCardDetails from '@/components/cards/RecordCardDetails.vue';
import LayoutBase from '@/layouts/LayoutBase.vue';
import ModalBase from '@/components/pinesUI/ModalBase.vue';
import TableBase from '@/components/table/TableBase.vue';
import useModal from '@/composables/useModal';

const recordSortingOptions: GenericSortOption<RecordResource>[] = [
    {
        title: 'Date',
        value: 'created_at',
        disabled: false,
    },
    {
        title: 'Title',
        value: 'video_name',
        disabled: false,
    },
    {
        title: 'Folder',
        value: 'folder_name',
        disabled: false,
    },
];

const { pageTitle, selectedSideBar } = storeToRefs(useAppStore());
const { data: stateRecords, isFetching: isLoading } = useRecords();
const { deleteRecord } = useRecord();

const confirmModal = useModal({ title: 'Delete Record?', submitText: 'Confim' });
const cachedID = ref<number | null>(null);

const recordsSortKey = ref<keyof RecordResource>(recordSortingOptions[0].value);
const recordsSortDir = ref<SortDir>(1);
const searchQuery = ref('');

const sortedRecords = computed<RecordResource[]>(() => {
    if (isLoading.value || !stateRecords.value) return [];
    return [...stateRecords.value].sort(sortObject<RecordResource>(recordsSortKey.value, recordsSortDir.value, ['created_at', 'updated_at']));
});

// Filters sorted records by search query
const filteredRecords = computed<RecordResource[]>(() => {
    const tempList = searchQuery.value
        ? sortedRecords.value.filter((record: RecordResource) => {
              try {
                  const strRepresentation = [record.video_name ?? record.file_name, record.folder_name, record.created_at, record.category?.name].join(' ').toLowerCase();
                  return strRepresentation.includes(searchQuery.value.toLowerCase());
              } catch (error) {
                  console.log(error);
                  return false;
              }
          })
        : sortedRecords.value;
    return tempList;
});

const handleDelete = (id: number) => {
    cachedID.value = id;
    confirmModal.toggleModal(true);
};

const submitDelete = async () => {
    if (cachedID.value) {
        const request = await deleteRecord.mutateAsync(cachedID.value);
        if (request) toast.add('Success', { type: 'success', description: 'Record Deleted Successfully!', life: 3000 });
        else toast.add('Error', { type: 'warning', description: 'Unable to delete record. Please try again.', life: 3000 });
    }
};

const handleSort = (sortKey: keyof RecordResource, sortDir: SortDir) => {
    recordsSortDir.value = sortDir;
    recordsSortKey.value = sortKey;
};

onMounted(() => {
    pageTitle.value = 'History';
    selectedSideBar.value = '';
});
</script>

<template>
    <LayoutBase>
        <template v-slot:content>
            <section id="content-history" class="3xl:min-h-[60vh] space-y-2 lg:min-h-[80vh]">
                <TableBase
                    :data="filteredRecords"
                    :row="RecordCardDetails"
                    :clickAction="handleDelete"
                    :loading="isLoading"
                    :useToolbar="true"
                    :sort-action="handleSort"
                    :sortingOptions="recordSortingOptions"
                    v-model="searchQuery"
                />
            </section>
            <ModalBase :modalData="confirmModal" :action="submitDelete">
                <template #description> Are you sure you want to delete this record? </template>
            </ModalBase>
        </template>
        <template v-slot:sidebar> </template>
    </LayoutBase>
</template>
