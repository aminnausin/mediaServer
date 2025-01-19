import { type CategoryResource, type FolderResource, type TaskResource, type UserResource } from '@/types/resources';
import type { TaskStatsResponse } from '@/types/types';

import { useGetCategories, useGetLibraryFolders, useGetTasks, useGetTaskStats, useGetUsers } from '@/service/queries';
import { ref, watch, type Ref } from 'vue';
import { formatFileSize } from '@/service/util';
import { defineStore } from 'pinia';
import { useRoute } from 'vue-router';

export const useDashboardStore = defineStore('Dashboard', () => {
    const route = useRoute();

    const stateLibraryId = ref<number>(-1);

    const { data: rawCategories, isLoading: isLoadingLibraries } = useGetCategories();
    const { data: rawLibraryFolders, isLoading: isLoadingLibraryFolders } = useGetLibraryFolders(stateLibraryId);
    const { data: rawUsers, isLoading: isLoadingUsers } = useGetUsers();
    const { data: rawTasks, isLoading: isLoadingTasks } = useGetTasks();
    const { data: rawTaskStats, isLoading: isLoadingTaskStats } = useGetTaskStats();

    const stateLibraries = ref<CategoryResource[]>([]);
    const stateLibraryFolders = ref<FolderResource[]>([]);
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

    watch(rawLibraryFolders, (v: any) => {
        if (!v?.data) return;
        stateLibraryFolders.value = v.data ?? [];
    });

    watch(
        () => route?.params?.id,
        (URL_ID) => {
            stateLibraryId.value = parseInt(`${URL_ID}`) && parseInt(`${URL_ID}`) > 0 ? parseInt(`${URL_ID}`) : -1;
        },
        { immediate: true },
    );

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
        stateLibraryFolders,
        stateTasks,
        stateUsers,
        stateTaskStats,
        stateTotalLibrariesSize,
        isLoadingLibraries,
        isLoadingLibraryFolders,
        stateLibraryId,
        isLoadingUsers,
        isLoadingTasks,
        updateSingleTask,
        updateSingleLibrary,
    } as {
        stateLibraries: Ref<CategoryResource[]>;
        stateLibraryFolders: Ref<FolderResource[]>;
        stateTasks: Ref<TaskResource[]>;
        stateUsers: Ref<UserResource[]>;
        stateTaskStats: Ref<TaskStatsResponse>;
        stateTotalLibrariesSize: Ref<string>;
        isLoadingLibraries: Ref<boolean>;
        isLoadingLibraryFolders: Ref<boolean>;
        stateLibraryId: Ref<number>;
        isLoadingUsers: Ref<boolean>;
        isLoadingTasks: Ref<boolean>;
        updateSingleTask: (data: TaskResource) => void;
        updateSingleLibrary: (data: CategoryResource) => void;
    };
});
