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

export function getUsers() {
    return API.get('/users');
}

export function getActiveSessions() {
    return API.get('/active-sessions');
}

export function getTasks() {
    return API.get('/tasks');
}

export function getSubTasks(taskId: number) {
    return API.post(`/sub-tasks/${taskId}`);
}

export function getTaskStats() {
    return API.get('/tasks/stats');
}

export function startGenericTast(url: string) {
    return API.post(`/tasks/${url}`);
}

export function startIndexFilesTask(category?: number) {
    return API.post(`/tasks/index/${category ?? ''}`);
}

export function startSyncFilesTask() {
    return API.post(`/tasks/sync`);
}

export function startVerifyFilesTask(category?: number) {
    return API.post(`/tasks/verify/${category ?? ''}`);
}

export function startScanFilesTask(category?: number) {
    return API.post(`/tasks/scan/${category ?? ''}`);
}

export function deleteTask(taskId: number) {
    return API.delete(`/tasks/${taskId}`);
}

export function cancelTask(taskId: number) {
    return API.post(`/tasks/cancel/${taskId}`);
}

export function deleteSubTask(taskId: number) {
    return API.delete(`/sub-tasks/${taskId}`);
}
