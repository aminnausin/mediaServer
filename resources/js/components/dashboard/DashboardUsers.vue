<script setup lang="ts">
import type { BreadCrumbItem } from '@/types/types';
import type { UserResource } from '@/types/resources';

import { useDashboardStore } from '@/stores/DashboardStore';
import { useQueryClient } from '@tanstack/vue-query';
import { computed, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { deleteUser } from '@/service/siteAPI';
import { sortObject } from '@/service/sort/baseSort';
import { TableBase } from '@/components/cedar-ui/table';
import { toast } from '@aminnausin/cedar-ui';

import BreadCrumbs from '@/components/pinesUI/BreadCrumbs.vue';
import ButtonText from '@/components/inputs/ButtonText.vue';
import ModalBase from '@/components/pinesUI/ModalBase.vue';
import UserCard from '@/components/cards/UserCard.vue';
import useModal from '@/composables/useModal';

import ProiconsArrowSync from '~icons/proicons/arrow-sync';
import ProiconsHome2 from '~icons/proicons/home-2';
import ProiconsAdd from '~icons/proicons/add';
import LucideUsers from '~icons/lucide/users';

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

const breadCrumbs = computed(() => {
    const items: BreadCrumbItem[] = [
        {
            name: 'Dashboard',
            url: '/dashboard/analytics',
            icon: ProiconsHome2,
        },
        {
            name: 'Users',
            url: '/dashboard/users',
            icon: LucideUsers,
        },
    ];

    return items;
});

const { stateUsers, isLoadingUsers, stateActiveSessions } = storeToRefs(useDashboardStore());

const filteredUsers = computed(() => {
    const tempList = searchQuery.value
        ? stateUsers.value.filter((user: UserResource) => {
              try {
                  const strRepresentation = [user.name, user.email, user.created_at].join(' ').toLowerCase();

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
    const tempList = [...stateUsers.value]
        .map((user) => {
            return { ...user, last_active: user.last_active || 0 };
        })
        .sort(sortObject(column, dir, ['created_at', 'last_active']));
    stateUsers.value = tempList as UserResource[];
    return tempList;
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

const loadData = async (refresh: boolean = false) => {
    if (refresh) toast.info('Refreshing Data');
    await queryClient.refetchQueries({ queryKey: ['activeSessions'] });
    await queryClient.refetchQueries({ queryKey: ['users'] });
};
</script>

<template>
    <div class="flex flex-wrap items-center justify-between gap-2">
        <BreadCrumbs :bread-crumbs="breadCrumbs" />

        <span class="flex gap-2 overflow-clip">
            <p class="font-medium capitalize">Users: {{ stateUsers.length }}</p>
            <p class="font-medium capitalize">Active: {{ stateActiveSessions ?? 0 }}</p>
        </span>
        <div class="xs:*:h-8 flex w-full flex-wrap items-center gap-2 *:h-fit">
            <ButtonText title="Create new user" @click="toast.add('Success', { type: 'success', description: 'User Created!', life: 3000 })" disabled>
                <template #text>New User</template>
                <template #icon><ProiconsAdd /></template>
            </ButtonText>
            <ButtonText @click="loadData" title="Refresh User List">
                <template #text>Refresh</template>
                <template #icon><ProiconsArrowSync /></template>
            </ButtonText>
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
        v-model="searchQuery"
    />
    <ModalBase :modalData="confirmModal" :action="submitDelete">
        <template #description> Are you sure you want to remove this user? </template>
    </ModalBase>
</template>
