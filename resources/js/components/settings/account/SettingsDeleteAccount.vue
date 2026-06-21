<script setup lang="ts">
import { useModalStore } from '@/stores/ModalStore';
import { ButtonForm } from '@/components/cedar-ui/button';

import DeleteAccountModal from '@/components/modals/DeleteAccountModal.vue';
import SettingsHeader from '@/components/settings/SettingsHeader.vue';
import SettingsCard from '@/components/cards/layout/SettingsCard.vue';
import FormFooter from '@/components/forms/FormFooter.vue';

const modal = useModalStore();
</script>

<template>
    <SettingsCard>
        <SettingsHeader>
            <h3 class="text-base font-medium">Account Management</h3>
            <p class="text-foreground-1">Permanently delete your account</p>
        </SettingsHeader>

        <FormFooter class="mt-auto w-fit flex-1 text-nowrap">
            <ButtonForm variant="danger" title="Delete your account permanently" @click="modal.open(DeleteAccountModal)"> Delete Account </ButtonForm>
        </FormFooter>
    </SettingsCard>
</template>
