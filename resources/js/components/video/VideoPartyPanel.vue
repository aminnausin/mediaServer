<script setup lang="ts">
import type { UserResource } from '@/types/resources';

import { useAuthStore } from '@/stores/AuthStore';
import { storeToRefs } from 'pinia';
import { toast } from '@/service/toaster/toastService';
import { ref } from 'vue';

import VideoPartyItem from '@/components/video/VideoPartyItem.vue';
import VideoPopover from '@/components/video/VideoPopover.vue';
import VideoButton from '@/components/video/VideoButton.vue';

import LucideLogOut from '~icons/lucide/log-out';
import ProiconsEye from '~icons/proicons/eye';
import ProiconsAdd from '~icons/proicons/add';

defineProps<{ player?: HTMLVideoElement }>();

const { userData } = storeToRefs(useAuthStore());

const partyUsers = ref<UserResource[]>([
    {
        id: 2560,
        name: 'zutomayo',
        email: 'test2@tmu.ca',
        last_active: 'today',
        created_at: '2025-03-02',
    },
    {
        id: 2561,
        name: 'purpleschala',
        email: 'test3@tmu.ca',
        last_active: 'today',
        created_at: '2025-03-02',
    },
]);

const handleKickUser = (id: number) => {
    toast.info('Kicked user [username]');
    partyUsers.value = partyUsers.value.filter((user) => user.id !== id);
};
</script>

<template>
    <VideoPopover
        v-if="userData?.id"
        popoverClass="max-w-40! rounded-lg right-4"
        ref="popover-party"
        :margin="80"
        :player="player ?? undefined"
        :force-popover-position="'bottom'"
        button-class="hover:bg-neutral-900/60 bg-neutral-900/30 p-1 rounded-full hover:scale-100 scale-90 transition-transform ease-in-out duration-500 flex gap-1 items-center justify-center"
        title="Watch Party"
    >
        <template #buttonIcon>
            <ProiconsEye class="h-4 w-4" />
            <p>{{ 1 + partyUsers.length }}</p>
        </template>
        <template #content>
            <section class="scrollbar-minimal flex h-12 flex-col gap-2 overflow-y-auto p-1 text-xs transition-transform xs:h-24 md:h-fit">
                <section class="flex justify-between">
                    <h3>Party ({{ 1 + partyUsers.length }}/8)</h3>
                    <span class="flex justify-end gap-1">
                        <VideoButton :icon="ProiconsAdd" title="Invite to Party" @click="toast('Would open friends list', { type: 'info' })" />
                        <VideoButton :icon="LucideLogOut" title="Leave Party" @click="toast('Would leave party', { type: 'info' })" />
                    </span>
                </section>
                <VideoPartyItem v-for="user in [userData, ...partyUsers]" :user="user" :key="user.id" :leader-id="1" @kick-user="handleKickUser" />
            </section>
        </template>
    </VideoPopover>
</template>
