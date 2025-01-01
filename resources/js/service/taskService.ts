import { startIndexFilesTask, startScanFilesTask, startSyncFilesTask, startVerifyFilesTask } from './siteAPI';
import { toast } from './toaster/toastService';

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

        toast.add('Success', { type: 'success', description: `Submitted ${job} Request!` });
    } catch (error) {
        toast('Failure', { type: 'danger', description: `Unable to submit ${job} request.` });
    }
}
