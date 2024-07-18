<script setup>
    import { storeToRefs } from 'pinia';
    import { useAuthStore } from '../stores/AuthStore'
    import Layout from '../components/layout/Layout.vue';
    import { onMounted, ref } from 'vue';
    import RecordFull from '../components/RecordFull.vue';
    const authStore = useAuthStore();

    const {auth, csrfToken} = storeToRefs(authStore)

    const records = ref([]);

    async function loadHistory(){
        if(!auth) return;
        fetch(`/api/records`, {
            method: 'get',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        }).then((response) => 
            response.json()
        ).then((json) => {
            // console.log(json);
            if(json.success == true){
                parseHistory(json.data);
            }
        }).catch((error) => {
            console.log(error);
        });
    }

    async function deleteRecord(id){
        const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const RECORDID = parseInt(id);
        if(isNaN(RECORDID)){
            toastr.error(`Invalid Record ID... ${RECORDID}}`);
            return;
        }

        fetch(`/api/records/${RECORDID}`, {
            method: 'delete',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF
            }
        }).then((response) => 
            response.json()
        ).then((json) => {
            if(json.success == true) {
                let newRecordsList = records.value.filter((record) => { 
                    return record.recordID != RECORDID;
                });
                records.value = newRecordsList;
                toastr.success('Record deleted!');
            };
        }).catch((error) => {
            console.log(error);
            toastr.error('Unable to delete record.');
        });
    }

    function parseHistory(data, empty = true) {
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
            
            newData.push({videoName, folderName, timeSpan, date:`${rawDate.toLocaleDateString([], {year: "numeric", month: '2-digit', day: '2-digit', hour: '2-digit', hour12:false, minute:'2-digit'})}`, recordID})
        }

        records.value = newData;
    }

    onMounted(() => {
        loadHistory();  
    })
</script>

<template>
    <Layout>
        <template v-slot:content>
            <section id="content-history" class=" space-y-2 cursor-pointer min-h-[80vh] pt-8">
                <RecordFull v-for="record in records" :record="record" :key="record.recordID" @deleteRecord="deleteRecord(record.recordID)"/>
            </section>
        </template>
        
    </Layout>
</template>