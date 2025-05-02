import { startIndexFilesTask, startScanFilesTask, startSyncFilesTask, startVerifyFilesTask, startVerifyFoldersTask } from '@/service/siteAPI';
import { subscribeToTask } from '@/service/wsService';
import { useAppStore } from '@/stores/AppStore';
import { toast } from '@/service/toaster/toastService';

export async function handleStartTask(job: 'index' | 'sync' | 'verify' | 'scan' | 'verifyFolders', libraryId?: number) {
    try {
        const result =
            job === 'index'
                ? await startIndexFilesTask()
                : job === 'sync'
                  ? await startSyncFilesTask()
                  : job === 'verify'
                    ? await startVerifyFilesTask(libraryId)
                    : job === 'verifyFolders'
                      ? await startVerifyFoldersTask(libraryId)
                      : await startScanFilesTask(libraryId);

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
