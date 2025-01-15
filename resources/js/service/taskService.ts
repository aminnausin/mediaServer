import { startIndexFilesTask, startScanFilesTask, startSyncFilesTask, startVerifyFilesTask } from '@/service/siteAPI';
import { subscribeToTask } from '@/service/wsService';
import { toast } from '@/service/toaster/toastService';
import { useAppStore } from '@/stores/AppStore';

export async function handleStartTask(job: 'index' | 'sync' | 'verify' | 'scan') {
    try {
        const result =
            job === 'index'
                ? await startIndexFilesTask()
                : job === 'sync'
                  ? await startSyncFilesTask()
                  : job === 'verify'
                    ? await startVerifyFilesTask()
                    : await startScanFilesTask();

        const task: { task_id: number; message: string } = result.data;
        const { createEcho } = useAppStore();

        createEcho();

        subscribeToTask(task.task_id);
        toast.add('Success', { type: 'success', description: task?.message ?? `Submitted ${job} Request!` });
    } catch (error) {
        toast('Failure', { type: 'danger', description: `Unable to submit ${job} request.` });
    }
}
