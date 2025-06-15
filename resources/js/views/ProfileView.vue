<script setup lang="ts">
import { useAppStore } from '../stores/AppStore';
import { storeToRefs } from 'pinia';
import { onMounted } from 'vue';
import { toast } from '@/service/toaster/toastService';

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
                <section
                    id="user-info"
                    class="w-full flex justify-between gap-4 p-3 rounded-xl shadow-lg dark:bg-primary-dark-800/70 bg-primary-800 z-[3] text-neutral-600 dark:text-neutral-400"
                >
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
                    </div>
                    <div class="h-6 flex gap-2 text-sm">
                        <ButtonText text="Add Friend" @click="toast.error('Friend not added...', { description: 'You do not know why...', life: 30000, position: 'top-right' })" />
                        <ButtonText text="Block" />
                    </div>
                </section>
            </section>
        </template>
    </LayoutBase>
</template>
