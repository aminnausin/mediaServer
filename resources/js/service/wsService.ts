import { useDashboardStore } from '@/stores/DashboardStore';
import { toast } from '@/service/toaster/toastService';
import { useAppStore } from '@/stores/AppStore';

/**
 * Subscribes to a specific task ID and shows a notification once it ends.
 *
 * @param taskId The specific Task ID
 */
export function subscribeToTask(taskId: number) {
    if (isNaN(taskId) || taskId <= 0 || window.Echo == null) return;

    window.Echo.private(`tasks.${taskId}`).listen('TaskEnded', async (event: any) => {
        if (!window.Echo || window.Echo?.connector?.pusher?.connection?.state !== 'connected') return;

        if (event?.task) {
            toast.add(`"${event?.task?.name}" ${event?.task?.status}.`, { type: event?.task?.status_key > 0 ? 'success' : 'danger' });
            const { updateSingleTask } = useDashboardStore();
            updateSingleTask(event.task);
        }

        window.Echo.leave(`tasks.${taskId}`);

        setTimeout(() => {
            if (Object.keys(window.Echo?.connector?.channels).length === 0) {
                const { disconnectEcho } = useAppStore();
                disconnectEcho();
            }
        }, 1000);
    });

    return {
        unsubscribe: async () => {
            unsubscribeFromChannel(`tasks.${taskId}`, true);
        },
    };
}

export function subscribeToDaskboardTasks() {
    if (window.Echo == null) return;

    window.Echo.private(`dashboard.tasks`).listen('TaskUpdated', (event: any) => {
        if (!window.Echo || window.Echo?.connector?.pusher?.connection?.state !== 'connected') return;
        if (!event.task) return;

        const { updateSingleTask } = useDashboardStore();
        updateSingleTask(event.task);
    });

    return {
        unsubscribe: async () => {
            unsubscribeFromChannel('dashboard.tasks');
        },
    };
}

function unsubscribeFromChannel(channel: string, closeOnEmpty: boolean = false) {
    if (!window.Echo || window.Echo?.connector?.pusher?.connection?.state !== 'connected') return;

    // Not manually leaving anymore because the websocket is closed by the time it tries to leave
    // window.Echo.leave(channel);

    if (closeOnEmpty && Object.keys(window.Echo.connector?.channels).length === 0) {
        const { disconnectEcho } = useAppStore();
        disconnectEcho();
    }
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
 */
