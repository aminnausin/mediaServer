<script setup lang="ts">
import type { UserResource } from '@/types/resources';

import { useAuthStore } from '@/stores/AuthStore';
import { storeToRefs } from 'pinia';
import { toast } from '@aminnausin/cedar-ui';
import { ref } from 'vue';

import PlayerToolbarButton from '@/components/video/button/PlayerToolbarButton.vue';
import VideoPartyItem from '@/components/video/plugins/party/VideoPartyItem.vue';
import VideoButton from '@/components/video/button/VideoButton.vue';

import LucideLogOut from '~icons/lucide/log-out';
import ProiconsEye from '~icons/proicons/eye';
import ProiconsAdd from '~icons/proicons/add';

defineProps<{ isShowingParty?: boolean }>();

const { userData } = storeToRefs(useAuthStore());

const isShowingPanel = ref(false);
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
    <Teleport defer to="#player-toolbar" v-if="userData?.id && isShowingParty">
        <PlayerToolbarButton @click="isShowingPanel = !isShowingPanel" :is-active="isShowingPanel" :class="['ps-1']" title="Open watch party panel">
            <ProiconsEye class="size-4" />
            <p>{{ 1 + partyUsers.length }}</p>
        </PlayerToolbarButton>
    </Teleport>
    <div v-if="userData?.id" v-show="isShowingPanel" class="pointer-events-auto w-fit max-w-40 rounded-md border border-neutral-700/10 bg-neutral-800/90 p-2 backdrop-blur-xs">
        <div class="scrollbar-minimal scrollbar-dark xs:max-h-24 xs:h-fit flex h-12 flex-col gap-2 overflow-y-auto text-xs">
            <div class="flex items-center justify-between gap-2">
                <p>Party ({{ 1 + partyUsers.length }}/8)</p>
                <span class="flex justify-end gap-1">
                    <VideoButton
                        :icon="ProiconsAdd"
                        class="flex size-5 items-center justify-center p-0 *:size-3.5"
                        title="Invite to Party"
                        @click="toast('Would open friends list', { type: 'info' })"
                    />
                    <VideoButton
                        :icon="LucideLogOut"
                        class="flex size-5 items-center justify-center p-0 *:size-3.5"
                        title="Leave Party"
                        @click="toast('Would leave party', { type: 'info' })"
                    />
                </span>
            </div>
            <VideoPartyItem v-for="user in [userData, ...partyUsers]" :user="user" :key="user.id" :leader-id="1" @kick-user="handleKickUser" />
        </div>
    </div>
</template>
