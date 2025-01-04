import { useDashboardStore } from '@/stores/DashboardStore';
import { toast } from '@/service/toaster/toastService';

/**
 * Subscribes to a specific task ID and shows a notification once it ends.
 *
 * @param taskId The specific Task ID
 */
export function subscribeToTask(taskId: number) {
    if (isNaN(taskId) || taskId <= 0) return;

    window.Echo.private(`tasks.${taskId}`).listen('TaskEnded', (event: any) => {
        toast.add(`"${event?.task?.name}" ${event?.task?.status}.`, { type: event?.task?.status_key > 0 ? 'success' : 'danger' });

        window.Echo.leave(`tasks.${taskId}`);
    });
    return { unsubscribe: () => window.Echo.leave(`tasks.${taskId}`) };
}

export function subscribeToDaskboardTasks() {
    window.Echo.private(`dashboard.tasks`).listen('TaskUpdated', (event: any) => {
        // console.log(event);
        // Update StateTasks??
        if (!event.task) return;

        const { updateSingleTask } = useDashboardStore();
        updateSingleTask(event.task);
    });

    return { unsubscribe: () => window.Echo.leave('dashboard.tasks') };
}

/**
 * WS Layout
 *
 * Running Tasks:
 *
 *  when starting a task, connect to tasks.(taskID)
 *
 *  when task is finished => toast notification with state and then close the connection
 *
 *  no need to authenticate with user id in channel name because task access is set by user permissions and task status is not private to one task.
 *
 *  Auth is done through user permissions in backend (id == 1)
 *
 * Dashboard tasks page:
 *
 *  On Mount, connect to dashboard.tasks
 *  On UnMount, leave dashboard.tasks
 *
 *  listen to TaskUpdated Event and update stateTask where task.id matches the updated data.
 *
 *  Auth is done through user permissions in backend (id == 1)
 *
 * Dashboard Libraries Page:
 *
 *  On Mount, connect to dashboard.libraries
 *  On UnMount, leave dashboard.libraries
 *
 *  listen to LibraryUpdated Event and update stateLibraries where library.id matches the updated data.
 *
 *  I guess event runs when library edited instead of loading data again and when index files creates files or folders
 *
 *  Auth is done through user permissions in backend (id == 1)
 *
 *
 *
 */
