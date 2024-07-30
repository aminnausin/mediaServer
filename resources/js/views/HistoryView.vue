<script setup>
    import RecordCardDetails from '../components/cards/RecordCardDetails.vue';
    import LayoutBase from '../components/layout/LayoutBase.vue';
    import ModalBase from '../components/pinesUI/ModalBase.vue';
    import useModal from '../composables/useModal';
    import TableBase from '../components/table/TableBase.vue';

    import { storeToRefs } from 'pinia';
    import { useAppStore } from '../stores/AppStore';
    import { useContentStore } from '../stores/ContentStore';
    import { onMounted, ref } from 'vue';

    
    const appStore = useAppStore();
    const ContentStore = useContentStore();
    const { pageTitle, selectedSideBar } = storeToRefs(appStore);
    const { records } = storeToRefs(ContentStore);
    const { getRecords, deleteRecord } = ContentStore;
    const loaded = ref(false);

    const cachedID = ref(null);
    const confirmModal = useModal({title: 'Delete Record?', submitText: 'Confim'});

    const handleDelete = (id) => {
        cachedID.value = id;
        confirmModal.toggleModal(true);
        console.log(confirmModal.modalOpen);
    }

    const submitDelete = async () => {
        if(cachedID.value) await deleteRecord(cachedID.value)
    }

    onMounted(() => {
        pageTitle.value = "History";
        selectedSideBar.value = '';
        (async () => {
            if( records.value.length <= 10 ) await getRecords();  
            loaded.value = true;
        })()
    })
</script>

<template>
    <LayoutBase>
        <template v-slot:content>
            <section id="content-history" class=" space-y-2 cursor-pointer min-h-[80vh] ">
                <!-- <RecordCardDetails v-for="record in records" :data="record" :key="record.id" @deleteRecord="handleDelete(record.id)"/> -->

                <TableBase :data="records" :row="RecordCardDetails" :clickAction="handleDelete">

                </TableBase>
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