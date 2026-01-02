import type { CategoryResource, FolderResource, TaskResource, UserResource } from '@/types/resources';
import type { TaskStatsResponse } from '@/types/types';

import { useGetActiveSessions, useGetCategories, useGetLibraryFolders, useGetTasks, useGetTaskStats, useGetUsers } from '@/service/queries';
import { formatFileSize } from '@/service/util';
import { defineStore } from 'pinia';
import { ref, watch } from 'vue';
import { useRoute } from 'vue-router';

export const useDashboardStore = defineStore('Dashboard', () => {
    const route = useRoute();

    const stateLibraryId = ref<number>(-1);

    const { data: rawCategories, isLoading: isLoadingLibraries } = useGetCategories();
    const { data: rawLibraryFolders, isLoading: isLoadingLibraryFolders } = useGetLibraryFolders(stateLibraryId);
    const { data: rawUsers, isLoading: isLoadingUsers } = useGetUsers();
    const { data: rawTasks, isLoading: isLoadingTasks } = useGetTasks();
    const { data: rawTaskStats, isLoading: isLoadingTaskStats } = useGetTaskStats();
    const { data: rawActiveSessions, isLoading: isLoadingActiveSessions } = useGetActiveSessions();

    const stateLibraries = ref<CategoryResource[]>([]);
    const stateLibraryFolders = ref<FolderResource[]>([]);
    const stateTasks = ref<TaskResource[]>([]);
    const stateUsers = ref<UserResource[]>([]);
    const stateActiveSessions = ref<number>(0);

    const stateTaskStats = ref<TaskStatsResponse>();
    const stateTotalLibrariesSize = ref();

    watch(rawCategories, (v) => {
        stateLibraries.value = v?.data ?? [];

        if (!v?.data) return;
        const totalSize = v.data.reduce((total: number, library: CategoryResource) => total + library.total_size, 0);

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

    watch(rawActiveSessions, (v: any) => {
        if (isNaN(parseInt(v))) return;
        stateActiveSessions.value = v ?? 0;
    });

    watch(
        () => route?.params?.id,
        (URL_ID) => {
            stateLibraryId.value = parseInt(`${URL_ID}`) && parseInt(`${URL_ID}`) > 0 ? parseInt(`${URL_ID}`) : -1; // ISSUE: using -1 as an empty value
        },
        { immediate: true },
    );

    const updateSingleTask = (data: TaskResource) => {
        const index = stateTasks.value.findIndex((task) => task.id === data.id);

        if (index === -1) {
            stateTasks.value = [data, ...stateTasks.value];
            return;
        }
        stateTasks.value = [...stateTasks.value.slice(0, index), data, ...stateTasks.value.slice(index + 1)];
    };

    const updateSingleLibrary = (data: CategoryResource) => {
        const index = stateLibraries.value.findIndex((lib) => lib.id === data.id);

        if (index === -1) {
            stateLibraries.value = [data, ...stateLibraries.value];
            return;
        }
        stateLibraries.value = [...stateLibraries.value.slice(0, index), data, ...stateLibraries.value.slice(index + 1)];
    };

    return {
        stateLibraries,
        stateLibraryFolders,
        stateTasks,
        stateUsers,
        stateTaskStats,
        stateLibraryId,
        stateTotalLibrariesSize,
        stateActiveSessions,
        isLoadingLibraries,
        isLoadingLibraryFolders,
        isLoadingUsers,
        isLoadingTasks,
        isLoadingTaskStats,
        isLoadingActiveSessions,
        updateSingleTask,
        updateSingleLibrary,
    };
});
