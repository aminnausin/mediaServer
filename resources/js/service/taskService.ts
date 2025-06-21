import { startIndexFilesTask, startScanFilesTask, startSyncFilesTask, startVerifyFilesTask, startVerifyFoldersTask } from '@/service/siteAPI';
import { subscribeToTask } from '@/service/wsService';
import { useAppStore } from '@/stores/AppStore';
import { toast } from '@/service/toaster/toastService';

declare type Job = 'index' | 'sync' | 'verify' | 'scan' | 'verifyFolders';

export async function handleStartTask(job: Job, libraryId?: number) {
    try {
        const result = await runTask(job, libraryId);
        const task: { task_id: number; message: string } = result.data;
        const { createEcho } = useAppStore();

        createEcho();

        subscribeToTask(task.task_id);
        toast.add('Success', { type: 'success', description: task?.message ?? `Submitted ${job} Request!` });
    } catch (error) {
        toast('Failure', { type: 'danger', description: `Unable to submit ${job} request.` });
        console.error(error);
    }
}

async function runTask(job: Job, libraryId?: number) {
    switch (job) {
        case 'index':
            return await startIndexFilesTask();
        case 'sync':
            return await startSyncFilesTask();
        case 'verify':
            return await startVerifyFilesTask(libraryId);
        case 'verifyFolders':
            return await startVerifyFoldersTask(libraryId);
        default:
            return await startScanFilesTask(libraryId);
    }
}
