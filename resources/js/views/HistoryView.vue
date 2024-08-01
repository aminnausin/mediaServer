<script setup>
import RecordCardDetails from '../components/cards/RecordCardDetails.vue';
import LayoutBase from '../components/layout/LayoutBase.vue';
import ModalBase from '../components/pinesUI/ModalBase.vue';
import useModal from '../composables/useModal';
import TableBase from '../components/table/TableBase.vue';

import { storeToRefs } from 'pinia';
import { useAppStore } from '../stores/AppStore';
import { useContentStore } from '../stores/ContentStore';
import { computed, onMounted, ref } from 'vue';


const appStore = useAppStore();
const ContentStore = useContentStore();
const loading = ref(true);
const cachedID = ref(null);
const confirmModal = useModal({ title: 'Delete Record?', submitText: 'Confim' });
const searchQuery = ref('');

const { pageTitle, selectedSideBar } = storeToRefs(appStore);
const { records } = storeToRefs(ContentStore);
const { getRecords, deleteRecord, recordsSort } = ContentStore;

const filteredRecords = computed(() => {
    let tempList = searchQuery.value ? records.value.filter((video) => {{
        try {
            let strRepresentation = [video.relationships?.video_name, video.relationships?.folder_name, video.attributes.created_at,].join(' ').toLowerCase();
            return strRepresentation.includes(searchQuery.value.toLowerCase())
        } catch (error) {
            console.log(error);
            return false
        }
    }}) : records.value;
    return tempList;
})

const handleDelete = (id) => {
    cachedID.value = id;
    confirmModal.toggleModal(true);
}

const submitDelete = async () => {
    if (cachedID.value) await deleteRecord(cachedID.value)
}

const sortingOptions = ref([
{
        title: 'Date',
        value: 'created_at',
        disabled: false
    },
    {
        title: 'Title',
        value: 'video_name',
        disabled: false
    },
    {
        title: 'Folder',
        value: 'folder_name',
        disabled: false
    },
]);

const handleSort = (column = 'date', dir = 1) =>{
    recordsSort(column, dir);
}

const handleSearch = (query) => {
    searchQuery.value = query;
}

onMounted(() => {
    pageTitle.value = "History";
    selectedSideBar.value = '';
    (async () => {
        if (records.value.length <= 10) await getRecords();
        // await getRecords();  
        loading.value = false;
    })()
})
</script>

<template>
    <LayoutBase>
        <template v-slot:content>
            <section id="content-history" class=" space-y-2 cursor-pointer min-h-[80vh] ">
                <TableBase :data="filteredRecords" :row="RecordCardDetails" :clickAction="handleDelete" :loading="loading" :useToolbar="true" :sortAction="handleSort" :sortingOptions="sortingOptions" @search="handleSearch"/>
            </section>
            <ModalBase :modalData="confirmModal" :action="submitDelete">
                <template #content>
                    <div class="relative w-auto pb-8">
                        <p>Are you sure you want to delete this record?</p>
                    </div>
                </template>
            </ModalBase>
        </template>
        <template v-slot:sidebar>
            <!-- <HistorySidebar /> -->
        </template>
    </LayoutBase>
</template>