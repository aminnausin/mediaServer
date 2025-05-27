<script setup lang="ts">
import { useAppStore } from '../stores/AppStore';
import { storeToRefs } from 'pinia';
import { onMounted } from 'vue';

import ProfileHeader from '@/components/profile/ProfileHeader.vue';
import ButtonText from '@/components/inputs/ButtonText.vue';
import LayoutBase from '@/layouts/LayoutBase.vue';

const { pageTitle, selectedSideBar } = storeToRefs(useAppStore());

onMounted(() => {
    pageTitle.value = 'User Profile';
    selectedSideBar.value = '';
});
</script>

<template>
    <LayoutBase>
        <template #content>
            <section id="content-profile" class="flex flex-col gap-3">
                <ProfileHeader />
                <section id="user-info" class="w-full flex justify-between">
                    <div class="flex flex-col gap-1">
                        <!-- <p>Views: 2343</p>
                        <p>Tags: 20</p>
                        <p>Edits: 20</p> -->
                    </div>
                    <div class="h-6 flex gap-2 text-sm">
                        <ButtonText text="Add Friend" />
                        <ButtonText text="Block" />
                    </div>
                </section>
            </section>
        </template>
    </LayoutBase>
</template>
