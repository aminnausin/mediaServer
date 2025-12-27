<script setup lang="ts">
import { useSettingsStore } from '@/stores/SettingsStore';
import { useModalStore } from '@/stores/ModalStore';
import { storeToRefs } from 'pinia';

import LogoutSessionsModal from '@/components/modals/LogoutSessionsModal.vue';
import SettingsHeader from '@/components/settings/SettingsHeader.vue';
import SettingsCard from '@/components/cards/layout/SettingsCard.vue';
import SessionCard from '@/components/cards/data/SessionCard.vue';
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
                <p class="text-foreground-1">Manage and log out your active sessions on other browsers and devices.</p>
            </SettingsHeader>

            <!-- Other Browser Sessions -->
            <section class="flex w-full max-w-xl flex-col gap-4">
                <div
                    v-if="isLoadingSessions || stateSessions.length === 0"
                    class="text-foreground-1 col-span-full my-auto flex w-full items-center justify-center gap-2 text-center tracking-wider uppercase"
                >
                    <p>{{ isLoadingSessions ? '...Loading' : 'No Sessions' }}</p>
                    <SvgSpinners90RingWithBg v-show="isLoadingSessions" />
                </div>
                <div
                    v-if="stateSessions.length > 0"
                    class="scrollbar-minimal scrollbar-track:bg-neutral-300 scrollbar-track:dark:bg-neutral-800 flex max-h-64 flex-col gap-2 overflow-y-auto"
                >
                    <SessionCard v-for="(session, index) in stateSessions" :key="index" :session="session" class="flex items-center" />
                </div>
                <div class="relative flex w-full flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <ButtonForm variant="submit" @click="modal.open(LogoutSessionsModal)">Log Out Other Sessions</ButtonForm>
                </div>
            </section>
        </template>
    </SettingsCard>
</template>
