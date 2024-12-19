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

export function getPulse(req?: { type?: string; period?: '' | '1_hour' | '6_hours' | '24_hours' | '7_days' }) {
    return API.get(`/pulse${req?.type ? `/${req?.type}` : ''}${req?.period ? `?period=${req?.period}` : ''}`);
}
