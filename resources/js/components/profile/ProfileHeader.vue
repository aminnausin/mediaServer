<script setup lang="ts">
import { useUserProfile } from '@/service/users/useUsers';
import { toTimeSpan } from '@/service/util';
import { useRoute } from 'vue-router';
import { computed } from 'vue';

const route = useRoute();
const username = computed(() => route.params.username.toString());

const { data: userProfile } = useUserProfile(username);
</script>
<template>
    <section
        id="banner"
        style="background-image: url('https://s4.anilist.co/file/anilistcdn/user/banner/b6792701-mBLPRvzr3xPL.jpg')"
        class="flex h-52 items-end overflow-clip bg-cover text-white lg:h-64"
    >
        <section id="profile-header" class="flex w-full flex-wrap items-end gap-4 bg-linear-to-b from-transparent to-neutral-950/40 p-3 text-center">
            <img
                :src="
                    userProfile?.name[0] === 'a'
                        ? '/storage/avatars/default.jpg'
                        : (userProfile?.avatar ?? `https://ui-avatars.com/api/?name=${userProfile?.name[0]}&amp;color=7F9CF5&amp;background=random`)
                "
                class="ring-primary-active/70 mx-auto aspect-square w-24 min-w-24 rounded-full object-cover ring-2 md:h-32 md:w-32"
                alt="profile"
            />
            <section class="text-centre flex flex-1 flex-wrap items-end justify-center sm:pb-2 xl:pb-4">
                <h2 class="w-full text-2xl capitalize sm:flex-1 sm:text-left">{{ userProfile?.name }}</h2>
                <p class="text-sm">Member since: {{ toTimeSpan(userProfile?.created_at || '', '') }}</p>
            </section>
        </section>
    </section>
</template>
