<script setup lang="ts">
import type { BreadCrumbItem, TaskStatsResponse } from '@/types/types';
import type { TaskResource } from '@/types/resources';

import { computed, onMounted, onUnmounted, ref, useTemplateRef, type Ref } from 'vue';
import { cancelTask, deleteSubTask, deleteTask } from '@/service/siteAPI';
import { subscribeToDaskboardTasks } from '@/service/wsService';
import { useDashboardStore } from '@/stores/DashboardStore';
import { useQueryClient } from '@tanstack/vue-query';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { sortObject } from '@/service/sort/baseSort';
import { toast } from '@/service/toaster/toastService';

import DashboardTaskMenu from '@/components/dashboard/DashboardTaskMenu.vue';
import BasePopover from '@/components/pinesUI/BasePopover.vue';
import BreadCrumbs from '@/components/pinesUI/BreadCrumbs.vue';
import ButtonText from '@/components/inputs/ButtonText.vue';
import ModalBase from '@/components/pinesUI/ModalBase.vue';
import TableBase from '@/components/table/TableBase.vue';
import TaskCard from '@/components/cards/TaskCard.vue';
import useModal from '@/composables/useModal';

import ProiconsArrowSync from '~icons/proicons/arrow-sync';
import ProiconsHome2 from '~icons/proicons/home-2';
import CircumServer from '~icons/circum/server';
import ProiconsAdd from '~icons/proicons/add';

const sortingOptions = [
    {
        title: 'Add Time',
        value: 'created_at',
        disabled: false,
    },
    {
        title: 'Start Time',
        value: 'started_at',
        disabled: false,
    },
    {
        title: 'End Time',
        value: 'ended_at',
        disabled: false,
    },
    {
        title: 'Status',
        value: 'status_key',
        disabled: false,
    },
    {
        title: 'Name',
        value: 'name',
        disabled: false,
    },
    {
        title: 'Task Length',
        value: 'sub_tasks_total',
        disabled: false,
    },
    {
        title: 'Task Duration',
        value: 'duration',
        disabled: false,
    },
];

const breadCrumbs = computed(() => {
    const items: BreadCrumbItem[] = [
        {
            name: 'Dashboard',
            url: '/dashboard/analytics',
            icon: ProiconsHome2,
        },
        {
            name: 'Tasks',
            url: '/dashboard/tasks',
            icon: CircumServer,
        },
    ];

    return items;
});

const { stateTasks, stateTaskStats, isLoadingTasks } = storeToRefs(useDashboardStore()) as {
    stateTasks: Ref<TaskResource[]>;
    stateTaskStats: Ref<TaskStatsResponse>;
    isLoadingTasks: Ref<boolean>;
};
const { createEcho, disconnectEcho } = useAppStore();

const liveUpdate = ref<any>(null);

const queryClient = useQueryClient();
const taskPopover = useTemplateRef('taskPopover');
const cancelModal = useModal({ title: 'Cancel task?', submitText: 'Confim' });
const deleteModal = useModal({ title: 'Remove task from records?', submitText: 'Confim' });

const isScreenSmall = ref(false);
const isScreenLarge = ref(false);
const searchQuery = ref('');
const cachedID = ref<number | null>(null);

const filteredTasks = computed(() => {
    const tempList = searchQuery.value
        ? stateTasks.value.filter((task: TaskResource) => {
              try {
                  const strRepresentation = [task.name, task.summary, task.description, task.created_at, task.status, task.id].join(' ').toLowerCase();
                  return strRepresentation.includes(searchQuery.value.toLowerCase());
              } catch (error) {
                  console.log(error);
                  return false;
              }
          })
        : stateTasks.value;
    return tempList;
});

const handleSort = async (column: keyof TaskResource = 'created_at', dir: -1 | 1 = 1) => {
    const tempList = [...stateTasks.value].sort(sortObject<TaskResource>(column, dir, ['created_at', 'started_at', 'ended_at']));
    stateTasks.value = tempList;
    return tempList;
};

const handleSearch = (query: string) => {
    searchQuery.value = query;
};

const handleDelete = (id: number, type: '' | 'cancel' | 'subTask' = '', innerId?: number) => {
    cachedID.value = id;
    if (type == 'cancel') cancelModal.toggleModal(true);
    else if (type == 'subTask') {
        if (!innerId && innerId !== 0) return;
        submitSubTaskDelete(innerId);
    } else submitDelete();
};

const submitCancel = async () => {
    if (!cachedID.value) {
        toast('Error', { type: 'danger', description: 'Invalid ID' });
        return;
    }

    try {
        await cancelTask(cachedID.value);
        toast.add('Success', { type: 'success', description: 'Task cancelled successfully!', life: 3000 });
        loadData();
    } catch (error) {
        toast.add('Error', { type: 'warning', description: 'Unable to cancel task. Please try again.', life: 3000 });
        console.error(error);
    }
};

const submitDelete = async () => {
    if (!cachedID.value) {
        toast('Error', { type: 'danger', description: 'Invalid ID' });
        return;
    }

    try {
        await deleteTask(cachedID.value);
        toast.add('Success', { type: 'success', description: 'Task deleted successfully!', life: 3000 });
        loadData();
    } catch (error) {
        toast.add('Error', { type: 'warning', description: 'Unable to delete task. Please try again.', life: 3000 });
        console.error(error);
    }
};

const submitSubTaskDelete = async (id: number) => {
    if (!id) {
        toast('Error', { type: 'danger', description: 'Invalid ID' });
        return;
    }

    try {
        await deleteSubTask(id);
        toast.add('Success', { type: 'success', description: `Sub Task ${id} deleted successfully!`, life: 3000 });
        loadData();
    } catch (error) {
        toast.add('Error', { type: 'warning', description: 'Unable to delete sub task. Please try again.', life: 3000 });
        console.error(error);
    }
};

const loadData = async () => {
    queryClient.invalidateQueries({ queryKey: ['tasks'] });
    queryClient.invalidateQueries({ queryKey: ['taskStats'] });
};

const updateScreenSize = () => {
    isScreenSmall.value = window.innerWidth < 640;
    isScreenLarge.value = window.innerWidth < 1024;
};

onMounted(() => {
    updateScreenSize();
    // For showing and hiding charts with a v-if instead of css for performance reasons
    window.addEventListener('resize', updateScreenSize);
    loadData();
    createEcho();
    if (window.Echo) liveUpdate.value = subscribeToDaskboardTasks();
});

onUnmounted(async () => {
    window.removeEventListener('resize', updateScreenSize);
    if (liveUpdate.value) liveUpdate.value?.unsubscribe().then(() => disconnectEcho());
    else disconnectEcho();
});
</script>

<template>
    <div class="flex items-center gap-2 justify-between flex-wrap">
        <BreadCrumbs :bread-crumbs="breadCrumbs" />

        <span class="flex overflow-clip gap-2 capitalize font-medium">
            <p class="">Running Tasks: {{ stateTaskStats?.count_running }}</p>
            <p class="">Total Tasks: {{ stateTasks.length ?? stateTaskStats?.count_tasks }}</p>
        </span>
        <div class="flex flex-wrap items-center gap-2 [&>*]:h-fit [&>*]:xs:h-8 w-full">
            <BasePopover popoverClass="!w-52 rounded-lg mt-10" :button-attributes="{ title: 'Start New Task' }" ref="taskPopover">
                <template #buttonText>New Task</template>
                <template #buttonIcon>
                    <ProiconsAdd />
                </template>
                <template #content>
                    <DashboardTaskMenu @handle-close="taskPopover?.handleClose" />
                </template>
            </BasePopover>
            <ButtonText
                @click="
                    () => {
                        loadData().then(() => {
                            toast.add('Success', { type: 'success', description: 'Data Refreshed!', life: 3000 });
                        });
                    }
                "
            >
                <template #text>Refresh</template>
                <template #icon><ProiconsArrowSync /></template>
            </ButtonText>
        </div>
    </div>
    <TableBase
        :use-pagination="true"
        :data="[...filteredTasks]"
        :row="TaskCard"
        :row-attributes="{ isScreenSmall, isScreenLarge }"
        :loading="isLoadingTasks"
        :clickAction="handleDelete"
        :sort-action="handleSort"
        :sorting-options="sortingOptions"
        :table-styles="'gap-4 xs:gap-2'"
        @search="handleSearch"
    />
    <ModalBase :modalData="cancelModal" :action="submitCancel">
        <template #description> Are you sure you want to cancel this task and all of its sub tasks? </template>
    </ModalBase>
    <ModalBase :modalData="deleteModal" :action="submitDelete">
        <template #description>Are you sure you want to delete this task and all of its sub tasks? </template>
    </ModalBase>
</template>
