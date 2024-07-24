import { ref } from 'vue';


/*
    ACTIONS for record data ? 

    Have get, add, and delete actions
    Run and commit to database (store)
*/
export function useRecords() {
    const records = ref([]);

    //GET
    async function getRecords(){
        if(!auth) return;
        fetch(`/api/records${limit ? `?limit=${limit}` : ''}`, {
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

    //POST
    async function addRecord(videoID){
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch(`/api/records`, {
            method: 'post',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                'video_id': videoID,
            })
        }).then((response) => 
            response.json()
        ).then((json) => {
            // console.log(json);
            if(json.success == true) {
                // toastr['success']('Added to history!');
                // loadHistory();
                parseHistory([json.data], 10, false);
            };
        }).catch((error) => {
            console.log(error);
        });
    }

    //DELETE
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
}