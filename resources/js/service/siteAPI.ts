/*
    ACTIONS for site management idk what to call this

    Have create, get, update actions for jobs?
    Stats endpoints for counts and graphs
    Run and commit to database (store) -> ?? What does that mean?
*/
import { API } from './api';

export function getSiteAnalytics(period?: string) {
    const parsedPeriod = period ? `?period=${period}` : '';
    return API.get(`/analytics${parsedPeriod}`);
}

export function getPulse(req?: { type?: string; period?: string }) {
    const parsedPeriod = req?.period ? `?period=${req.period}` : '';
    return API.get(`/pulse/${req?.type ?? ''}${parsedPeriod}`);
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

export function getTaskWaitTimes() {
    return API.get('/tasks/wait-times');
}

export function toggleCategoryPrivacy(category: number, value: boolean) {
    return API.post(`/categories/privacy/${category}`, { is_private: value });
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
export function startVerifyFoldersTask(category?: number) {
    return API.post(`/tasks/verify-folders/${category ?? ''}`);
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

export function deleteUser(userId: number) {
    return API.delete(`/users/${userId}`);
}

export function getManifest() {
    return API.get('/manifest');
}
