<script setup lang="ts">
import { useModalStore } from '@/stores/ModalStore';
import { ButtonForm } from '@/components/cedar-ui/button';

import DeleteAccountModal from '@/components/modals/DeleteAccountModal.vue';
import SettingsHeader from '@/components/settings/SettingsHeader.vue';
import SettingsCard from '@/components/cards/layout/SettingsCard.vue';

const modal = useModalStore();
</script>

<template>
    <SettingsCard>
        <template #content>
            <SettingsHeader>
                <h3 class="text-base font-medium">Account Management</h3>
                <p class="text-foreground-1">Permanently delete your account.</p>
            </SettingsHeader>

            <!-- Other Browser Sessions -->
            <div class="mt-auto flex h-8 w-full max-w-xl flex-col sm:flex-row sm:justify-end">
                <ButtonForm variant="danger" class="" title="Delete your account permanently" @click="modal.open(DeleteAccountModal)"> Delete Account </ButtonForm>
            </div>
        </template>
    </SettingsCard>
</template>
