<script setup lang="ts">
import type { ProfileResource } from '@/types/resources';

import { getProfileByName } from '@/service/profileService';
import { onMounted, ref } from 'vue';
import { toTimeSpan } from '@/service/util';
import { useRoute } from 'vue-router';

const router = useRoute();
const userProfile = ref<ProfileResource>();

onMounted(async () => {
    const userIdentifier = router.params.username.toString();

    try {
        const { data } = await getProfileByName(userIdentifier);

        if (data) {
            userProfile.value = data;
        }
    } catch (error) {
        console.log(error);
    }
});
</script>
<template>
    <section
        id="banner"
        style="background-image: url('https://s4.anilist.co/file/anilistcdn/user/banner/b6792701-mBLPRvzr3xPL.jpg')"
        class="flex h-52 items-end overflow-clip rounded-md bg-cover text-white shadow-md lg:h-64"
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
                <h3 class="text-sm">Member since: {{ toTimeSpan(userProfile?.created_at || '', '') }}</h3>
            </section>
        </section>
    </section>
</template>
