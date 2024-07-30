<script setup>
    import RecordCardDetails from '../components/cards/RecordCardDetails.vue';
    import LayoutBase from '../components/layout/LayoutBase.vue';
    import ModalBase from '../components/pinesUI/ModalBase.vue';
    import useModal from '../composables/useModal';

    import { storeToRefs } from 'pinia';
    import { useAppStore } from '../stores/AppStore';
    import { useContentStore } from '../stores/ContentStore';
    import { onMounted, ref } from 'vue';

    
    const appStore = useAppStore();
    const ContentStore = useContentStore();
    const { pageTitle } = storeToRefs(appStore);
    const { records } = storeToRefs(ContentStore);
    const { getRecords, deleteRecord } = ContentStore;

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
        getRecords();  
        pageTitle.value = "History";
    })
</script>

<template>
    <LayoutBase>
        <template v-slot:content>
            <section id="content-history" class=" space-y-2 cursor-pointer min-h-[80vh] pt-8">
                <RecordCardDetails v-for="record in records" :record="record" :key="record.recordID" @deleteRecord="handleDelete(record.id)"/>
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