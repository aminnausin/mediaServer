<script setup lang="ts">
import type { UserResource } from '@/types/resources';
import type { Ref } from 'vue';

import { useAuthStore } from '@/stores/AuthStore';
import { storeToRefs } from 'pinia';

import ButtonCorner from '@/components/inputs/ButtonCorner.vue';

import ProiconsCancel from '~icons/proicons/cancel';
import CircumStar from '~icons/circum/star';

const props = withDefaults(defineProps<{ user: UserResource; leaderId: number }>(), {});

const { userData } = storeToRefs(useAuthStore()) as { userData: Ref<UserResource> };

const emits = defineEmits<{
    kickUser: [id: number];
}>();
</script>
<template>
    <section class="flex justify-between">
        <p class="w-full flex-1">{{ user.name }}</p>
        <span class="flex gap-1 justify-end">
            <CircumStar v-if="leaderId === user.id" class="w-4 h-4" title="Party Leader" />
            <ButtonCorner
                :title="'Kick from party'"
                colour-classes="hover:bg-transparent"
                text-classes="hover:text-rose-600"
                position-classes="w-4 h-4 p-0"
                v-else-if="leaderId === userData?.id"
                @click="emits('kickUser', user.id)"
            >
                <template #icon><ProiconsCancel /></template>
            </ButtonCorner>
            <img
                class="h-4 w-4 rounded-full object-cover aspect-square"
                :src="`https://ui-avatars.com/api/?name=${user.name[0]}&amp;color=7F9CF5&amp;background=random`"
                :alt="'user profile picture'"
            />
        </span>
    </section>
</template>
