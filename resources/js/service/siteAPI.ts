/*
    ACTIONS for site management idk what to call this

    Have create, get, update actions for jobs?
    Stats endpoints for counts and graphs
    Run and commit to database (store) -> ?? What does that mean?
*/
import { API } from './api';

export function getStats() {
    return API.get(`/site/`);
}

export function getPulse(type?: string) {
    return API.get(`/pulse/${type ?? ''}`);
}
