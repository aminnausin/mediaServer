<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { ButtonText } from '@/components/cedar-ui/button';
import { toast } from '@aminnausin/cedar-ui';

import ProfileHeader from '@/components/profile/ProfileHeader.vue';
import LayoutBase from '@/layouts/LayoutBase.vue';

const { pageTitle, selectedSideBar } = storeToRefs(useAppStore());

const blockedFriends = ref(0);

onMounted(() => {
    pageTitle.value = 'User Profile';
    selectedSideBar.value = '';
});
</script>

<template>
    <LayoutBase>
        <template #content>
            <section id="content-profile" class="flex flex-col gap-3">
                <ProfileHeader class="ring-r-default/5 rounded-xl shadow-md ring-1" />
                <div id="user-info" class="bg-surface-2 text-foreground-1 ring-r-default/5 z-3 flex w-full flex-col justify-between gap-4 rounded-xl p-3 text-sm shadow-md ring-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="flex-1 text-base">Who?</h3>
                        <div class="flex flex-wrap gap-2">
                            <ButtonText text="Add Friend" @click="toast.error('Friend not added...', { description: 'You do not know why...' })" class="flex-10" />
                            <ButtonText
                                text="Block"
                                class="flex-1"
                                @click="
                                    () => {
                                        blockedFriends += 1;
                                        toast.info('Friend Blocked', { description: `You have blocked ${blockedFriends} friend(s). How nice of you.` });
                                    }
                                "
                            />
                        </div>
                    </div>

                    <div class="flex flex-col gap-1">
                        <p class="whitespace-pre">
                            {{
                                `Recently Watched:
[Video]  [Video]  [Video]

Recently Listended:
[Song]  [Song]  [Song]

Favorites:
[Video]  [Folder]  [Song]

Socials:
[Anilist]

Stats: 12 items watched | 9 hours total`
                            }}
                        </p>
                        <p class="mt-16 text-xs">This page is a work in progress if it wasn't obvious</p>
                    </div>
                </div>
            </section>
        </template>
    </LayoutBase>
</template>
