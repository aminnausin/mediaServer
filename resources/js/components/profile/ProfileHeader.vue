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
        class="bg-cover flex rounded-md overflow-clip h-52 lg:h-64 text-white items-end shadow-md"
    >
        <section id="profile-header" class="p-3 flex gap-4 bg-gradient-to-b from-transparent to-neutral-950/40 w-full items-end flex-wrap text-center">
            <img
                :src="
                    userProfile?.name[0] === 'a'
                        ? '/storage/avatars/default.jpg'
                        : (userProfile?.avatar ?? `https://ui-avatars.com/api/?name=${userProfile?.name[0]}&amp;color=7F9CF5&amp;background=random`)
                "
                class="md:h-32 md:w-32 w-24 min-w-24 rounded-full ring-2 ring-violet-700/70 object-cover aspect-square mx-auto"
                alt="profile"
            />
            <section class="text-centre flex flex-wrap flex-1 justify-center items-end sm:pb-2 xl:pb-4">
                <h2 class="text-2xl capitalize w-full sm:text-left sm:flex-1">{{ userProfile?.name }}</h2>
                <h3 class="text-sm">Member since: {{ toTimeSpan(userProfile?.created_at || '', '') }}</h3>
            </section>
        </section>
    </section>
</template>
