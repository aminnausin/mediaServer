/*
    ACTIONS for record data ? 

    Have get, add, and delete actions
    Run and commit to database (store) -> ?? What does that mean?
*/
import { API } from "./api"

export default{
    createRecord(data) {
        return API.post('/records', data)
    },
    getRecords(query) {
        return API.get(`/records${query}`);
    },
    deleteRecord(query) {
        return API.delete(`/records${query}`);
    }
}