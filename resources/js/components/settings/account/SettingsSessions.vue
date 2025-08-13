<script setup lang="ts">
import { useSettingsStore } from '@/stores/SettingsStore';
import { useModalStore } from '@/stores/ModalStore';
import { storeToRefs } from 'pinia';

import LogoutSessionsModal from '@/components/modals/LogoutSessionsModal.vue';
import SettingsHeader from '@/components/settings/SettingsHeader.vue';
import SettingsCard from '@/components/cards/SettingsCard.vue';
import SessionCard from '@/components/cards/SessionCard.vue';
import ButtonForm from '@/components/inputs/ButtonForm.vue';

import SvgSpinners90RingWithBg from '~icons/svg-spinners/90-ring-with-bg';

const { stateSessions, isLoadingSessions } = storeToRefs(useSettingsStore());

const modal = useModalStore();
</script>

<template>
    <SettingsCard class="group">
        <template #content>
            <SettingsHeader>
                <h3 class="text-base font-medium">Browser Sessions</h3>
                <p class="text-neutral-600 dark:text-neutral-400">Manage and log out your active sessions on other browsers and devices.</p>
            </SettingsHeader>

            <!-- Other Browser Sessions -->
            <section class="flex flex-col gap-4 w-full max-w-xl">
                <div
                    v-if="isLoadingSessions || stateSessions.length === 0"
                    class="col-span-full flex items-center justify-center text-center uppercase tracking-wider w-full gap-2 my-auto text-neutral-600 dark:text-neutral-400"
                >
                    <p>{{ isLoadingSessions ? '...Loading' : 'No Sessions' }}</p>
                    <SvgSpinners90RingWithBg v-show="isLoadingSessions" />
                </div>
                <div
                    v-if="stateSessions.length > 0"
                    class="flex flex-col gap-2 max-h-64 scrollbar-minimal scrollbar-track:bg-neutral-300 scrollbar-track:dark:bg-neutral-800 overflow-y-auto"
                >
                    <SessionCard v-for="(session, index) in stateSessions" :key="index" :session="session" class="flex items-center" />
                </div>
                <div class="relative flex flex-col-reverse sm:flex-row sm:justify-end gap-2 w-full">
                    <ButtonForm variant="submit" @click="modal.open(LogoutSessionsModal)">Log Out Other Sessions</ButtonForm>
                </div>
            </section>
        </template>
    </SettingsCard>
</template>
