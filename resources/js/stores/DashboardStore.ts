import { type CategoryResource, type FolderResource, type TaskResource, type UserResource } from '@/types/resources';

import { ref, watch, type Ref } from 'vue';
import { useGetCategories, useGetTasks, useGetTaskStats, useGetUsers } from '@/service/queries';
import { defineStore, storeToRefs } from 'pinia';
import type { TaskStatsResponse } from '@/types/types';
import { formatFileSize } from '@/service/util';
import { useAuthStore } from './AuthStore';

export const useDashboardStore = defineStore('Dashboard', () => {
    const { data: rawCategories, isLoading: isLoadingLibraries } = useGetCategories();
    const { data: rawUsers, isLoading: isLoadingUsers } = useGetUsers();
    const { data: rawTasks, isLoading: isLoadingTasks } = useGetTasks();
    const { data: rawTaskStats, isLoading: isLoadingTaskStats } = useGetTaskStats();

    const stateLibraries = ref<CategoryResource[]>([]);
    const stateTasks = ref<TaskResource[]>([]);
    const stateUsers = ref<UserResource[]>([]);

    const stateTaskStats = ref<TaskStatsResponse>();
    const stateTotalLibrariesSize = ref();

    watch(rawCategories, (v) => {
        stateLibraries.value = v?.data ?? [];

        if (!v?.data) return;
        let totalSize = v.data.reduce(
            (total: number, library: CategoryResource) => total + library.folders.reduce((total: number, folder: FolderResource) => total + Number(folder.total_size), 0),
            0,
        );

        if (!isNaN(totalSize)) stateTotalLibrariesSize.value = formatFileSize(totalSize);
    });

    watch(rawUsers, (v) => {
        stateUsers.value = v?.data ?? [];
    });

    watch(rawTasks, (v) => {
        stateTasks.value = v?.data ?? [];
    });

    watch(rawTaskStats, (v: any) => {
        if (!v) return;
        stateTaskStats.value = v;
    });

    const updateSingleTask = (data: TaskResource) => {
        if (stateTasks.value.findIndex((task) => task.id === data.id) === -1) {
            stateTasks.value = [data, ...stateTasks.value];
            return;
        }
        stateTasks.value = stateTasks.value.map((task) => (task.id === data.id ? data : task));
    };

    const updateSingleLibrary = (data: CategoryResource) => {
        if (stateLibraries.value.findIndex((lib) => lib.id === data.id) === -1) {
            stateLibraries.value = [data, ...stateLibraries.value];
            return;
        }
        stateLibraries.value = stateLibraries.value.map((lib) => (lib.id === data.id ? data : lib));
    };

    return {
        stateLibraries,
        stateTasks,
        stateUsers,
        stateTaskStats,
        stateTotalLibrariesSize,
        isLoadingLibraries,
        isLoadingUsers,
        isLoadingTasks,
        updateSingleTask,
        updateSingleLibrary,
    } as {
        stateLibraries: Ref<CategoryResource[]>;
        stateTasks: Ref<TaskResource[]>;
        stateUsers: Ref<UserResource[]>;
        stateTaskStats: Ref<TaskStatsResponse>;
        stateTotalLibrariesSize: Ref<string>;
        isLoadingLibraries: Ref<boolean>;
        isLoadingUsers: Ref<boolean>;
        isLoadingTasks: Ref<boolean>;
        updateSingleTask: (data: TaskResource) => void;
        updateSingleLibrary: (data: CategoryResource) => void;
    };
});
