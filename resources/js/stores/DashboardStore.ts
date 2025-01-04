import { type CategoryResource, type TaskResource, type UserResource } from '@/types/resources';

import { ref, watch, type Ref } from 'vue';
import { useGetCategories, useGetUsers } from '@/service/queries';
import { defineStore } from 'pinia';

export const useDashboardStore = defineStore('Dashboard', () => {
    const { data: rawCategories, isLoading: isLoadingLibraries } = useGetCategories();
    const { data: rawUsers, isLoading: isLoadingUsers } = useGetUsers();

    const stateLibraries = ref<CategoryResource[]>([]);
    const stateTasks = ref<TaskResource[]>([]);
    const stateUsers = ref<UserResource[]>([]);

    watch(rawCategories, (v) => {
        stateLibraries.value = v?.data ?? [];
    });

    watch(rawUsers, (v) => {
        stateUsers.value = v?.data ?? [];
    });

    return {
        stateLibraries,
        stateTasks,
        stateUsers,
        isLoadingLibraries,
        isLoadingUsers,
    } as {
        stateLibraries: Ref<CategoryResource[]>;
        stateTasks: Ref<TaskResource[]>;
        stateUsers: Ref<UserResource[]>;
        isLoadingLibraries: boolean;
        isLoadingUsers: boolean;
    };
});
