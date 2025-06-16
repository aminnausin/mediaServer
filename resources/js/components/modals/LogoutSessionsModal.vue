<script setup lang="ts">
import { signOutOtherSessions } from '@/service/authAPI';
import { useModalStore } from '@/stores/ModalStore';

import PasswordConfirm from '@/components/forms/PasswordConfirm.vue';
import BaseModal from '@/components/modals/BaseModal.vue';

const props = defineProps<{ onSuccess?: () => void }>();
const modalStore = useModalStore();

function handleSuccess() {
    modalStore.close();
    props.onSuccess?.();
}
</script>

<template>
    <BaseModal>
        <template #description>Are you sure you want to sign out of other devices? You won't be able to undo this action.</template>
        <PasswordConfirm :action="signOutOtherSessions" success-message="Other Sessions Logged Out Successfully" @confirm="handleSuccess" @cancel="modalStore.close" />
    </BaseModal>
</template>
