<script setup lang="ts">
import { deleteAccount } from '@/service/authAPI';
import { useRouter } from 'vue-router';

import PasswordConfirm from '@/components/forms/PasswordConfirm.vue';
import SettingsHeader from '@/components/settings/SettingsHeader.vue';
import SettingsCard from '@/components/cards/SettingsCard.vue';
import ButtonForm from '@/components/inputs/ButtonForm.vue';
import ModalBase from '@/components/pinesUI/ModalBase.vue';
import useModal from '@/composables/useModal';

const confirmModal = useModal({ title: 'Are you sure you want to delete your account?', submitText: 'Confim' });
const router = useRouter();
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
                        @click="confirmModal.toggleModal()"
                    >
                        Delete Account
                    </ButtonForm>
                </div>
            </section>
        </template>
    </SettingsCard>

    <ModalBase :modalData="confirmModal" :useControls="false">
        <template #description>
            Once your account is deleted, all of its resources and data will also be permanently deleted. Please enter your password to confirm you would like to permanently delete
            your account.
        </template>
        <template #content>
            <PasswordConfirm
                :action="deleteAccount"
                @cancel="confirmModal.toggleModal()"
                :success-action="
                    () => {
                        confirmModal.toggleModal();
                        router.push('/');
                    }
                "
                success-message="Account Deleted..."
            />
        </template>
    </ModalBase>
</template>
