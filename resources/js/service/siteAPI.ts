/*
    ACTIONS for site management idk what to call this

    Have create, get, update actions for jobs?
    Stats endpoints for counts and graphs
    Run and commit to database (store) -> ?? What does that mean?
*/
import { API } from './api';

export default {
    getStats() {
        return API.get(`/site/`);
    },
};
