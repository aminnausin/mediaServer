<script setup lang="ts">
import type { UserResource } from '@/types/resources';

import { ref, type Ref } from 'vue';
import { useAuthStore } from '@/stores/AuthStore';
import { storeToRefs } from 'pinia';
import { toast } from '@/service/toaster/toastService';

import VideoPartyItem from '@/components/video/VideoPartyItem.vue';
import VideoPopover from '@/components/video/VideoPopover.vue';
import VideoButton from '@/components/video/VideoButton.vue';

import LucideLogOut from '~icons/lucide/log-out';
import ProiconsEye from '~icons/proicons/eye';
import ProiconsAdd from '~icons/proicons/add';

const props = defineProps<{ player?: HTMLVideoElement }>();

const isLeader = ref(true);

const { userData } = storeToRefs(useAuthStore()) as { userData: Ref<UserResource> };
const { auth } = useAuthStore();

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
        popoverClass="!max-w-40 rounded-lg"
        ref="popover-party"
        :margin="80"
        :player="player ?? undefined"
        :force-popover-position="'bottom'"
        button-class="hover:bg-neutral-900/30 bg-neutral-900/10 p-1 rounded-full hover:scale-100 scale-90 transition-transform ease-in-out duration-500 flex gap-1 items-center justify-center"
        title="Watch Party"
    >
        <template #buttonIcon>
            <ProiconsEye class="w-4 h-4" />
            <p>{{ 1 + partyUsers.length }}</p>
        </template>
        <template #content>
            <section class="flex flex-col text-xs h-12 xs:h-24 md:h-fit overflow-y-auto scrollbar-minimal transition-transform gap-2 p-1">
                <section class="flex justify-between">
                    <h3>Party ({{ 1 + partyUsers.length }}/8)</h3>
                    <span class="flex gap-1 justify-end">
                        <VideoButton :icon="ProiconsAdd" title="Invite to Party" @click="toast('Would open friends list', { type: 'info' })" />
                        <VideoButton :icon="LucideLogOut" title="Leave Party" @click="toast('Would leave party', { type: 'info' })" />
                    </span>
                </section>
                <VideoPartyItem v-for="user in [userData, ...partyUsers]" :user="user" :key="user.id" :leader-id="1" @kick-user="handleKickUser" />
            </section>
        </template>
    </VideoPopover>
</template>
