<script setup lang="ts">
import type { UserResource } from '@/types/resources';
import type { Ref } from 'vue';

import { ButtonCorner } from '@/components/cedar-ui/button';
import { useAuthStore } from '@/stores/AuthStore';
import { storeToRefs } from 'pinia';

import ProiconsCancel from '~icons/proicons/cancel';
import CircumStar from '~icons/circum/star';

const props = withDefaults(defineProps<{ user: UserResource; leaderId: number }>(), {});

const { userData } = storeToRefs(useAuthStore()) as { userData: Ref<UserResource> };

const emits = defineEmits<{
    kickUser: [id: number];
}>();
</script>
<template>
    <section class="flex justify-between gap-2">
        <p class="w-full flex-1 truncate">{{ user.name }}</p>
        <span class="flex justify-end gap-1">
            <CircumStar v-if="leaderId === user.id" class="size-4" title="Party Leader" />
            <ButtonCorner
                :title="'Kick from party'"
                colour-classes="hover:bg-transparent"
                text-classes="hover:text-danger-2"
                position-classes="size-4 p-0"
                v-else-if="leaderId === userData?.id"
                @click="emits('kickUser', user.id)"
            >
                <template #icon><ProiconsCancel /></template>
            </ButtonCorner>
            <img
                class="aspect-square size-4 rounded-full object-cover"
                :src="`https://ui-avatars.com/api/?name=${user.name[0] ?? 'a'}&amp;color=7F9CF5&amp;background=random`"
                :alt="'user profile'"
            />
        </span>
    </section>
</template>
