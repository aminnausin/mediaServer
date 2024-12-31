<script setup lang="ts">
import type { TaskStatsResponse } from '@/types/types';
import type { TaskResource } from '@/types/resources';

import {
    cancelTask,
    deleteTask,
    getTasks,
    getTaskStats,
    startIndexFilesTask,
    startSyncFilesTask,
    startVerifyFilesTask,
} from '@/service/siteAPI';

import { computed, onMounted, onUnmounted, ref, useTemplateRef } from 'vue';
import { toast } from '@/service/toaster/toastService';

import ButtonText from '@/components/inputs/ButtonText.vue';
import ModalBase from '@/components/pinesUI/ModalBase.vue';
import TableBase from '@/components/table/TableBase.vue';
import TaskCard from '@/components/cards/TaskCard.vue';
import useModal from '@/composables/useModal';
import Popover from '@/components/pinesUI/Popover.vue';

import LucideFolderSearch from '~icons/lucide/folder-search';
import LucideFolderCheck from '~icons/lucide/folder-check';
import ProiconsArrowSync from '~icons/proicons/arrow-sync';
import LucideFolderTree from '~icons/lucide/folder-tree';
import LucideFolderSync from '~icons/lucide/folder-sync';
import ProiconsAdd from '~icons/proicons/add';

const cancelModal = useModal({ title: 'Cancel task?', submitText: 'Confim' });
const deleteModal = useModal({ title: 'Remove task from records?', submitText: 'Confim' });

const searchQuery = ref('');
const sortingOptions = ref([
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
]);

const cachedID = ref<number | null>(null);
const taskStats = ref<TaskStatsResponse>();
const tasks = ref<TaskResource[]>([]);
const isScreenSmall = ref(false);

const taskPopover = useTemplateRef('taskPopover');

const filteredTasks = computed(() => {
    let tempList = searchQuery.value
        ? tasks.value.filter((task: TaskResource) => {
              {
                  try {
                      let strRepresentation = [task.name, task.summary, task.description, task.created_at].join(' ').toLowerCase();
                      return strRepresentation.includes(searchQuery.value.toLowerCase());
                  } catch (error) {
                      console.log(error);
                      return false;
                  }
              }
          })
        : tasks.value;
    return tempList;
});

const handleSort = async (column = 'date', dir = 1) => {
    let tempList = tasks.value.sort((taskA: TaskResource, taskB: TaskResource) => {
        if (column === 'created_at' || column === 'started_at' || column === 'ended_at') {
            let dateA = new Date(taskA?.[column] ?? '');
            let dateB = new Date(taskB?.[column] ?? '');
            return (dateB.getTime() - dateA.getTime()) * dir;
        }

        let valueA = taskA[column as keyof TaskResource];
        let valueB = taskB[column as keyof TaskResource];
        if (valueA && valueB && typeof valueA === 'number' && typeof valueB === 'number') return (valueA - valueB) * dir;
        return `${valueA}`?.localeCompare(`${valueB}`) * dir;
    });
    tasks.value = tempList;

    return tempList;
};

const handleSearch = (query: string) => {
    searchQuery.value = query;
};

const handleDelete = (id: number, cancel: boolean = false) => {
    cachedID.value = id;
    if (cancel) cancelModal.toggleModal(true);
    else deleteModal.toggleModal(true);
};

const submitCancel = async () => {
    if (!cachedID.value) return;
    let request = await cancelTask(cachedID.value);
    if (request) {
        toast.add('Success', { type: 'success', description: 'Task cancelled successfully!', life: 3000 });
        loadData();
    } else toast.add('Error', { type: 'warning', description: 'Unable to cancel task. Please try again.', life: 3000 });
};

const submitDelete = async () => {
    if (!cachedID.value) return;
    let request = await deleteTask(cachedID.value);
    if (request) {
        toast.add('Success', { type: 'success', description: 'Task deleted successfully!', life: 3000 });
        loadData();
    } else toast.add('Error', { type: 'warning', description: 'Unable to delete task. Please try again.', life: 3000 });
};

const loadData = async () => {
    const { data: rawTaskStats } = await getTaskStats();
    taskStats.value = rawTaskStats ?? undefined;

    const { data: rawTasks } = await getTasks();
    tasks.value = rawTasks?.data?.length > 0 ? rawTasks.data : [];
};

const handleStartTask = async (job: 'index' | 'sync' | 'verify' | 'scan') => {
    try {
        const result =
            job === 'index' ? await startIndexFilesTask() : job === 'sync' ? await startSyncFilesTask() : await startVerifyFilesTask();

        toast.add('Success', { type: 'success', description: `Submitted ${job} Request!` });
    } catch (error) {
        toast('Failure', { type: 'danger', description: `Unable to submit ${job} request.` });
    }

    taskPopover.value?.handleClose();
};

const updateScreenSize = () => {
    isScreenSmall.value = window.innerWidth < 640;
};

onMounted(() => {
    updateScreenSize();
    window.addEventListener('resize', updateScreenSize);
    loadData();
});

onUnmounted(() => {
    window.removeEventListener('resize', updateScreenSize);
});
</script>

<template>
    <section id="tasks" class="flex gap-8 flex-col">
        <div class="flex items-start gap-2 justify-between flex-wrap">
            <div class="flex flex-wrap items-center gap-2 [&>*]:h-fit [&>*]:xs:h-8">
                <Popover popoverClass="!w-52 rounded-lg " :button-attributes="{ title: 'Start New Task' }" ref="taskPopover">
                    <template #buttonText>New Task</template>
                    <template #buttonIcon>
                        <ProiconsAdd />
                    </template>
                    <template #content>
                        <div class="grid gap-4">
                            <div class="space-y-2">
                                <h4 class="font-medium leading-none">Start Server Task</h4>
                            </div>

                            <div class="grid gap-2">
                                <ButtonText
                                    class="h-8 dark:!bg-neutral-950"
                                    :title="'Scan for Folder Changes'"
                                    @click="handleStartTask('index')"
                                >
                                    <template #text> Index Files </template>
                                    <template #icon> <LucideFolderSearch class="-order-1 h-4 w-4" /></template>
                                </ButtonText>
                                <ButtonText
                                    class="h-8 dark:!bg-neutral-950"
                                    :title="'Sync Folder With Database'"
                                    @click="handleStartTask('sync')"
                                >
                                    <template #text> Sync Files </template>
                                    <template #icon> <LucideFolderSync class="-order-1 h-4 w-4" /></template>
                                </ButtonText>
                                <ButtonText
                                    class="h-8 dark:!bg-neutral-950 disabled:opacity-60"
                                    :title="'Scan for New Metadata'"
                                    @click="handleStartTask('verify')"
                                >
                                    <template #text> Verify Metadata </template>
                                    <template #icon> <LucideFolderCheck class="-order-1 h-4 w-4" /></template>
                                </ButtonText>

                                <ButtonText
                                    class="h-8 text-rose-600 dark:!bg-rose-700 disabled:opacity-60"
                                    :title="'Scan and Index All Files For Metadata'"
                                    @click.stop.prevent="handleStartTask('scan')"
                                    disabled
                                >
                                    <template #text> Scan All Files </template>
                                    <template #icon> <LucideFolderTree class="-order-1 h-4 w-4" /></template>
                                </ButtonText>
                            </div>
                        </div>
                    </template>
                </Popover>
                <ButtonText @click="toast.add('Success', { type: 'success', description: 'Submitted Scan Request!', life: 3000 })" disabled>
                    <template #text>Run Full Scan</template>
                    <template #icon><ProiconsArrowSync /></template>
                </ButtonText>
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
            <div class="capitalize text-sm font-medium text-neutral-600 dark:text-neutral-300 flex flex-col gap-1 w-fit text-end">
                <p class="w-fit">Running Tasks: {{ tasks.filter((task) => task.status === 'processing').length }}</p>
                <p class="w-fit">Total Tasks: {{ tasks.length }}</p>
            </div>
        </div>
        <TableBase
            :use-pagination="true"
            :data="[...filteredTasks]"
            :row="TaskCard"
            :row-attributes="{ isScreenSmall }"
            :loading="false"
            :clickAction="handleDelete"
            :sort-action="handleSort"
            :sorting-options="sortingOptions"
            :table-styles="'gap-4 xs:gap-2'"
            @search="handleSearch"
        />
    </section>
    <ModalBase :modalData="cancelModal" :action="submitCancel">
        <template #content>
            <div class="relative w-auto pb-8">
                <p>Are you sure you want to cancel this task and all of its sub tasks?</p>
            </div>
        </template>
    </ModalBase>
    <ModalBase :modalData="deleteModal" :action="submitDelete">
        <template #content>
            <div class="relative w-auto pb-8">
                <p>Are you sure you want to delete this task and all of its sub tasks?</p>
            </div>
        </template>
    </ModalBase>
</template>
