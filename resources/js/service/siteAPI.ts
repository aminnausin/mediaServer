/*
    ACTIONS for site management idk what to call this

    Have create, get, update actions for jobs?
    Stats endpoints for counts and graphs
    Run and commit to database (store) -> ?? What does that mean?
*/
import { API } from './api';

export function getSiteAnalytics(period?: string) {
    return API.get(`/analytics${period ? `?period=${period}` : ''}`);
}

export function getPulse(req?: { type?: string; period?: string }) {
    return API.get(`/pulse${req?.type ? `/${req?.type}` : ''}${req?.period ? `?period=${req?.period}` : ''}`);
}
