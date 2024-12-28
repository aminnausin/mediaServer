<script setup lang="ts">
import type { UserResource } from '@/types/resources';

import { getActiveSessions, getUsers, startIndexFilesTask, startSyncFilesTask, startVerifyFilesTask } from '@/service/siteAPI';
import { computed, onMounted, ref, useTemplateRef } from 'vue';
import { toast } from '@/service/toaster/toastService';

import ButtonText from '@/components/inputs/ButtonText.vue';
import ModalBase from '@/components/pinesUI/ModalBase.vue';
import TableBase from '@/components/table/TableBase.vue';
import useModal from '@/composables/useModal';

import UserCard from '../cards/UserCard.vue';
import Popover from '../pinesUI/Popover.vue';

import ProiconsArrowSync from '~icons/proicons/arrow-sync';
import ProiconsAdd from '~icons/proicons/add';

import LucideFolderSync from '~icons/lucide/folder-sync';
import LucideFolderSearch from '~icons/lucide/folder-search';
import LucideFolderCheck from '~icons/lucide/folder-check';
import LucideFolderTree from '~icons/lucide/folder-tree';

const confirmModal = useModal({ title: 'Remove User?', submitText: 'Confim' });

const searchQuery = ref('');
const sortingOptions = ref([
    {
        title: 'Username',
        value: 'name',
        disabled: false,
    },
    {
        title: 'Date Joined',
        value: 'created_at',
        disabled: false,
    },
    {
        title: 'Last Active',
        value: 'last_active',
        disabled: false,
    },
]);

const activeSessions = ref(0);
const users = ref<UserResource[]>([]);
const cachedID = ref<number | null>(null);

const taskPopover = useTemplateRef('taskPopover');

const filteredUsers = computed(() => {
    let tempList = searchQuery.value
        ? users.value.filter((user: UserResource) => {
              {
                  try {
                      let strRepresentation = [user.name, user.email, user.created_at].join(' ').toLowerCase();
                      return strRepresentation.includes(searchQuery.value.toLowerCase());
                  } catch (error) {
                      console.log(error);
                      return false;
                  }
              }
          })
        : users.value;
    return tempList;
});

const handleSort = async (column = 'date', dir = 1) => {
    let tempList = users.value.sort((userA: UserResource, userB: UserResource) => {
        if (column === 'created_at' || column === 'last_active') {
            let dateA = new Date(userA?.[column] ?? '');
            let dateB = new Date(userB?.[column] ?? '');
            return (dateB.getTime() - dateA.getTime()) * dir;
        }
        return `${userB[column as keyof UserResource]}`?.localeCompare(`${userA[column as keyof UserResource]}`) * dir;
    });
    users.value = tempList;
    return tempList;
};

const handleSearch = (query: string) => {
    searchQuery.value = query;
};

const handleDelete = (id: number) => {
    cachedID.value = id;
    confirmModal.toggleModal(true);
};

const submitDelete = async () => {
    if (cachedID.value) {
        // let request = await deleteRecord(cachedID.value);
        let request = false;
        if (request) toast.add('Success', { type: 'success', description: 'User deleted successfully!', life: 3000 });
        else toast.add('Error', { type: 'warning', description: 'Unable to delete user. Please try again.', life: 3000 });
    }
};

const loadData = async () => {
    const { data: rawActiveSessions } = await getActiveSessions();
    activeSessions.value = parseInt(rawActiveSessions) ?? 0;

    const { data: rawUsers } = await getUsers();

    users.value = rawUsers?.data?.length > 0 ? rawUsers.data : [];
};

const handleStartTask = async (job: 'index' | 'sync' | 'verify') => {
    try {
        const result =
            job === 'index' ? await startIndexFilesTask() : job === 'sync' ? await startSyncFilesTask() : await startVerifyFilesTask();

        toast.add('Success', { type: 'success', description: `Submitted ${job} Request!` });
    } catch (error) {
        toast('Failure', { type: 'danger', description: `Unable to submit ${job} request.` });
    }

    taskPopover.value?.handleClose();
};

onMounted(() => {
    loadData();
});
</script>

<template>
    <section id="tasks" class="flex gap-8 flex-col">
        <div class="flex items-start gap-2 justify-between flex-wrap">
            <div class="flex flex-wrap items-center gap-2 [&>*]:h-fit [&>*]:xs:h-8">
                <Popover popoverClass="w-52 rounded-lg " :button-attributes="{ title: 'Start New Task' }" ref="taskPopover">
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
                                    @click.stop.prevent="$emit('clickAction')"
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
                <p class="w-fit">Running Tasks: {{ activeSessions }}</p>
                <p class="w-fit">Failed Tasks: {{ users.length }}</p>
            </div>
        </div>
        <TableBase
            :use-pagination="true"
            :data="[...filteredUsers]"
            :row="UserCard"
            :loading="false"
            :clickAction="handleDelete"
            :sort-action="handleSort"
            :sorting-options="sortingOptions"
            :table-styles="'gap-4 xs:gap-2'"
            @search="handleSearch"
        />
    </section>
    <ModalBase :modalData="confirmModal" :action="submitDelete">
        <template #content>
            <div class="relative w-auto pb-8">
                <p>Are you sure you want to remove this user?</p>
            </div>
        </template>
    </ModalBase>
</template>
