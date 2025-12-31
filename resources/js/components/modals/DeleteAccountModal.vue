<script setup lang="ts">
import { deleteAccount } from '@/service/authAPI';
import { useModalStore } from '@/stores/ModalStore';
import { BaseModal } from '@/components/cedar-ui/modal';
import { useRouter } from 'vue-router';

import PasswordConfirm from '@/components/forms/PasswordConfirm.vue';

const modalStore = useModalStore();
const router = useRouter();

function handleSuccess() {
    modalStore.close();
    router.push('/');
}
</script>

<template>
    <BaseModal>
        <template #title>Are you sure you want to delete your account?</template>
        <template #description>
            Once your account is deleted, all of its resources and data will also be permanently deleted. Please enter your password to confirm you would like to permanently delete
            your account.
        </template>

        <PasswordConfirm :action="deleteAccount" success-message="Account Deleted..." @confirm="handleSuccess" @cancel="modalStore.close" />
    </BaseModal>
</template>
