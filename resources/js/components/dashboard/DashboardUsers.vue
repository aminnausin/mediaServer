<script setup lang="ts">
import type { UserResource } from '@/types/resources';

import { deleteUser, getActiveSessions, getUsers } from '@/service/siteAPI';
import { computed, onMounted, ref } from 'vue';
import { toast } from '@/service/toaster/toastService';

import ButtonText from '@/components/inputs/ButtonText.vue';
import ModalBase from '@/components/pinesUI/ModalBase.vue';
import TableBase from '@/components/table/TableBase.vue';
import UserCard from '@/components/cards/UserCard.vue';
import useModal from '@/composables/useModal';

import ProiconsArrowSync from '~icons/proicons/arrow-sync';
import ProiconsAdd from '~icons/proicons/add';
import { storeToRefs } from 'pinia';
import { useDashboardStore } from '@/stores/DashboardStore';

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
const { stateUsers, isLoadingUsers } = storeToRefs(useDashboardStore());

const cachedID = ref<number | null>(null);

const filteredUsers = computed(() => {
    let tempList = searchQuery.value
        ? stateUsers.value.filter((user: UserResource) => {
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
        : stateUsers.value;
    return tempList;
});

const handleSort = async (column = 'date', dir = 1) => {
    let tempList = [...stateUsers.value].sort((userA: UserResource, userB: UserResource) => {
        if (column === 'created_at' || column === 'last_active') {
            let dateA = new Date(userA?.[column] ?? '');
            let dateB = new Date(userB?.[column] ?? '');
            return (dateB.getTime() - dateA.getTime()) * dir;
        }
        let valueA = userA[column as keyof UserResource];
        let valueB = userB[column as keyof UserResource];
        if (valueA && valueB && typeof valueA === 'number' && typeof valueB === 'number') return (valueA - valueB) * dir;
        return `${valueA}`?.localeCompare(`${valueB}`) * dir;
    });
    stateUsers.value = tempList;
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
        try {
            await deleteUser(cachedID.value);
            toast.add('Success', { type: 'success', description: 'User deleted successfully!', life: 3000 });
            loadData();
        } catch (error) {
            toast.add('Error', { type: 'warning', description: 'Unable to delete user. Please try again.', life: 3000 });
            console.log(error);
        }
    }
};

const loadData = async () => {
    const { data: rawActiveSessions } = await getActiveSessions();
    activeSessions.value = !isNaN(parseInt(rawActiveSessions)) ? parseInt(rawActiveSessions) : 0;

    // const { data: rawUsers } = await getUsers();

    // stateUsers.value = rawUsers?.data?.length > 0 ? rawUsers.data : [];
};

onMounted(() => {
    loadData();
});
</script>

<template>
    <section id="tasks" class="flex gap-8 flex-col">
        <div class="flex items-start gap-2 justify-between flex-wrap">
            <div class="flex flex-wrap items-center gap-2 [&>*]:h-fit [&>*]:xs:h-8">
                <ButtonText title="Create new user" @click="toast.add('Success', { type: 'success', description: 'User Created!', life: 3000 })" disabled>
                    <template #text>New User</template>
                    <template #icon><ProiconsAdd /></template>
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
                <p class="w-fit">Users: {{ stateUsers.length }}</p>
                <p class="w-fit">Active: {{ activeSessions ?? 0 }}</p>
            </div>
        </div>
        <TableBase
            :use-pagination="true"
            :data="filteredUsers"
            :row="UserCard"
            :loading="isLoadingUsers"
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
