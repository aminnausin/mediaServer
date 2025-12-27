<script setup lang="ts">
import { useModalStore } from '@/stores/ModalStore';

import DeleteAccountModal from '@/components/modals/DeleteAccountModal.vue';
import SettingsHeader from '@/components/settings/SettingsHeader.vue';
import SettingsCard from '@/components/cards/layout/SettingsCard.vue';
import ButtonForm from '@/components/inputs/ButtonForm.vue';

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
            <section class="mt-auto flex w-full max-w-xl flex-col gap-4">
                <div class="relative flex w-full flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <ButtonForm
                        variant="submit"
                        class="bg-danger-2 hover:bg-rose-700 dark:hover:bg-rose-500"
                        title="Delete your account permanently"
                        @click="modal.open(DeleteAccountModal)"
                    >
                        Delete Account
                    </ButtonForm>
                </div>
            </section>
        </template>
    </SettingsCard>
</template>
