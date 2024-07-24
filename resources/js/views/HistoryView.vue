<script setup>
    import RecordFull from '../components/RecordFull.vue';
    import LayoutBase from '../components/layout/LayoutBase.vue';

    import { storeToRefs } from 'pinia';
    import { useAuthStore } from '../stores/AuthStore'
    import { useAppStore } from '../stores/AppStore';
    import { onMounted, ref } from 'vue';
    import { API } from '../service/api';

    
    const records = ref([]);
    const appStore = useAppStore();
    const authStore = useAuthStore();
    const { userData } = storeToRefs(authStore)
    const { pageTitle } = storeToRefs(appStore);


    async function loadHistory(){
        if(!userData.value) return;
        
        const { data, error } = await API.get(`/records`);

        if(error || !data?.success){
            console.log(error ?? data?.message);
            return;
        }

        parseHistory(data.data);
    }

    async function deleteRecord(id){
        const recordID = parseInt(id);
        const { data, error } = await API.delete(`/records/${recordID}`); 

        if(error || !data?.success){
            // eslint-disable-next-line no-undef
            toastr['error'](data?.message ?? 'Unable to delete record.');
            console.log(error ?? data?.message);
            return;
        }

        let newRecordsList = records.value.filter((record) => { 
            return record.recordID != recordID;
        });

        // eslint-disable-next-line no-undef
        toastr.success('Record deleted!');
        records.value = newRecordsList;
    }

    function parseHistory(data) {
        let newData = [];

        for (let recordCount = 0; recordCount < data.length; recordCount++) {
            const recordID = data[recordCount].id;
            const videoName = data[recordCount].relationships.video_name;
            const folderName = data[recordCount].relationships.folder_name;
            const categoryName = data[recordCount].relationships.category_name;
            const rawDate = (new Date(data[recordCount].attributes.created_at.replace(' ', 'T')));
            const rawAge = Date.now() - rawDate.getTime();
        
            const weeks = Math.round(rawAge / (1000 * 3600 * 24 * 7));
            const days = Math.round(rawAge / (1000 * 3600 * 24));
            const hours = Math.round(rawAge / (1000 * 3600));
            const minutes = Math.round(rawAge / (1000 * 60));
            const seconds = Math.round(rawAge / (1000));
        
            const timeSpan = weeks > 0 ? `${weeks} week${weeks > 1 ? 's' : ''} ago` : days > 0 ? `${days} day${days > 1 ? 's' : ''} ago` : hours > 0 ? `${hours} hour${hours > 1 ? 's' : ''} ago` : minutes > 0 ? `${minutes}m ago` : `${seconds}s ago`
            
            newData.push({videoName, folderName, timeSpan, date:`${rawDate.toLocaleDateString([], {year: "numeric", month: '2-digit', day: '2-digit', hour: '2-digit', hour12:false, minute:'2-digit'}), categoryName}`, recordID})
        }

        records.value = newData;
    }

    onMounted(() => {
        loadHistory();  
        pageTitle.value = "Full History";
    })
</script>

<template>
    <LayoutBase>
        <template v-slot:content>
            <section id="content-history" class=" space-y-2 cursor-pointer min-h-[80vh] pt-8">
                <RecordFull v-for="record in records" :record="record" :key="record.recordID" @deleteRecord="deleteRecord(record.recordID)"/>
            </section>
        </template>
        <template v-slot:sidebar>
            <!-- <HistorySidebar /> -->
        </template>
    </LayoutBase>
</template>