import { type CategoryResource, type TaskResource, type UserResource } from '@/types/resources';

import { ref, watch, type Ref } from 'vue';
import { useGetCategories, useGetTasks, useGetUsers } from '@/service/queries';
import { defineStore } from 'pinia';

export const useDashboardStore = defineStore('Dashboard', () => {
    const { data: rawCategories, isLoading: isLoadingLibraries } = useGetCategories();
    const { data: rawUsers, isLoading: isLoadingUsers } = useGetUsers();
    const { data: rawTasks, isLoading: isLoadingTasks } = useGetTasks();

    const stateLibraries = ref<CategoryResource[]>([]);
    const stateTasks = ref<TaskResource[]>([]);
    const stateUsers = ref<UserResource[]>([]);

    watch(rawCategories, (v) => {
        stateLibraries.value = v?.data ?? [];
    });

    watch(rawUsers, (v) => {
        stateUsers.value = v?.data ?? [];
    });

    watch(rawTasks, (v) => {
        stateTasks.value = v?.data ?? [];
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
        isLoadingLibraries,
        isLoadingUsers,
        isLoadingTasks,
        updateSingleTask,
        updateSingleLibrary,
    } as {
        stateLibraries: Ref<CategoryResource[]>;
        stateTasks: Ref<TaskResource[]>;
        stateUsers: Ref<UserResource[]>;
        isLoadingLibraries: Ref<boolean>;
        isLoadingUsers: Ref<boolean>;
        isLoadingTasks: Ref<boolean>;
        updateSingleTask: (data: TaskResource) => void;
        updateSingleLibrary: (data: CategoryResource) => void;
    };
});
