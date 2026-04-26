import type { TaskStatus } from '@/types/types';

export interface TaskResource {
    id: number;
    user: string;
    status: TaskStatus;
    status_key: number;
    name?: string;
    summary?: string;
    description?: string;
    url?: string;
    sub_tasks: SubTaskResource[];
    sub_tasks_total: number;
    sub_tasks_pending: number;
    sub_tasks_complete: number;
    sub_tasks_failed: number;
    duration: number;
    started_at?: string;
    ended_at?: string;
    created_at: string;
    updated_at: string;
}

export interface SubTaskResource {
    id: number;
    task_id: number;
    status: TaskStatus;
    status_key: number;
    name?: string;
    summary?: string;
    progress: number;
    duration: number;
    started_at?: string;
    ended_at?: string;
    created_at: string;
    updated_at: string;
}
