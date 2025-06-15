<script setup lang="ts">
import { useModalStore } from '@/stores/ModalStore';

import DeleteAccountModal from '@/components/modals/DeleteAccountModal.vue';
import SettingsHeader from '@/components/settings/SettingsHeader.vue';
import SettingsCard from '@/components/cards/SettingsCard.vue';
import ButtonForm from '@/components/inputs/ButtonForm.vue';

const modal = useModalStore();
</script>

<template>
    <SettingsCard>
        <template #content>
            <SettingsHeader>
                <h3 class="text-base font-medium">Account Management</h3>
                <p class="text-neutral-600 dark:text-neutral-400">Permanently delete your account.</p>
            </SettingsHeader>

            <!-- Other Browser Sessions -->
            <section class="flex flex-col gap-4 w-full max-w-xl mt-auto">
                <div class="relative flex flex-col-reverse sm:flex-row sm:justify-end gap-2 w-full">
                    <ButtonForm
                        variant="submit"
                        class="bg-rose-600 hover:bg-rose-700 dark:hover:bg-rose-500"
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
