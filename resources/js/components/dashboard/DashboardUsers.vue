<script setup lang="ts">
import type { UserResource } from '@/types/resources';

import { computed, onMounted, ref } from 'vue';
import { useDashboardStore } from '@/stores/DashboardStore';
import { useQueryClient } from '@tanstack/vue-query';
import { storeToRefs } from 'pinia';
import { deleteUser } from '@/service/siteAPI';
import { sortObject } from '@/service/util';
import { toast } from '@/service/toaster/toastService';

import ButtonText from '@/components/inputs/ButtonText.vue';
import ModalBase from '@/components/pinesUI/ModalBase.vue';
import TableBase from '@/components/table/TableBase.vue';
import UserCard from '@/components/cards/UserCard.vue';
import useModal from '@/composables/useModal';

import ProiconsArrowSync from '~icons/proicons/arrow-sync';
import ProiconsAdd from '~icons/proicons/add';

const confirmModal = useModal({ title: 'Remove User?', submitText: 'Confim' });

const queryClient = useQueryClient();
const cachedID = ref<number | null>(null);
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

const { stateUsers, isLoadingUsers, stateActiveSessions } = storeToRefs(useDashboardStore());

const filteredUsers = computed(() => {
    let tempList = searchQuery.value
        ? stateUsers.value.filter((user: UserResource) => {
              try {
                  let strRepresentation = [user.name, user.email, user.created_at].join(' ').toLowerCase();

                  return strRepresentation.includes(searchQuery.value.toLowerCase());
              } catch (error) {
                  console.log(error);
                  return false;
              }
          })
        : stateUsers.value;
    return tempList;
});

const handleSort = async (column: keyof UserResource = 'created_at', dir: -1 | 1 = 1) => {
    let tempList = [...stateUsers.value]
        .map((user) => {
            return { ...user, last_active: user.last_active || 0 };
        })
        .sort(sortObject<UserResource>(column as keyof UserResource, dir, ['created_at', 'last_active']));
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
    await queryClient.invalidateQueries({ queryKey: ['activeSessions'] });
    await queryClient.invalidateQueries({ queryKey: ['users'] });
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
                <p class="w-fit">Active: {{ stateActiveSessions ?? 0 }}</p>
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
