<script setup>
    import RecordCardDetails from '../components/cards/RecordCardDetails.vue';
    import LayoutBase from '../components/layout/LayoutBase.vue';

    import { storeToRefs } from 'pinia';
    import { useAppStore } from '../stores/AppStore';
    import { useContentStore } from '../stores/ContentStore';
    import { onMounted } from 'vue';

    
    const appStore = useAppStore();
    const ContentStore = useContentStore();
    const { pageTitle } = storeToRefs(appStore);
    const { records } = storeToRefs(ContentStore);
    const { getRecords, deleteRecord } = ContentStore;


    onMounted(() => {
        getRecords();  
        pageTitle.value = "History";
    })
</script>

<template>
    <LayoutBase>
        <template v-slot:content>
            <section id="content-history" class=" space-y-2 cursor-pointer min-h-[80vh] pt-8">
                <RecordCardDetails v-for="record in records" :record="record" :key="record.recordID" @deleteRecord="deleteRecord(record.id)"/>
            </section>
        </template>
        <template v-slot:sidebar>
            <!-- <HistorySidebar /> -->
        </template>
    </LayoutBase>
</template>