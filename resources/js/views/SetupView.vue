<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { getCategories } from '@/service/mediaAPI';
import { useAuthStore } from '@/stores/AuthStore';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';

import LayoutBase from '@/layouts/LayoutBase.vue';

import ProiconsLibrary from '~icons/proicons/library';
import LucideFolder from '~icons/lucide/folder';

const { pageTitle, selectedSideBar } = storeToRefs(useAppStore());
const { userData } = storeToRefs(useAuthStore());

const librariesAdded = ref(false);

onMounted(async () => {
    pageTitle.value = 'MediaServer Setup';
    selectedSideBar.value = '';
    const { data } = await getCategories();

    if (data?.data?.length !== 0) librariesAdded.value = true;
});
</script>

<template>
    <LayoutBase>
        <template v-slot:content>
            <section id="content-settings" class="flex flex-col gap-4 *:space-y-1 space-y-2 lg:min-h-[80vh] 3xl:min-h-[60vh]">
                <h2>Before you can start watching your media, you must complete these setup steps</h2>
                <ul class="text-sm flex flex-col gap-2">
                    <li class="flex flex-wrap gap-1 items-center">
                        <p>
                            1. <RouterLink to="/register" :class="`text-purple-500 underline decoration-1`">Register</RouterLink> for an account. The first one will be your
                            administrator account.
                        </p>
                        <p v-show="userData" class="ms-auto text-xs font-bold text-green-600 dark:text-green-700">Done!</p>
                    </li>
                    <li>
                        <span class="flex gap-1 items-start">
                            <p>2. Put your files in the data/media folder. You must follow this folder structure. An example url would be /library1/folder2</p>
                            <p v-show="librariesAdded" class="ms-auto text-xs font-bold text-green-600 dark:text-green-700">Done!</p>
                        </span>
                        <div class="p-2 w-full font-mono">
                            <ul>
                                <li class="font-bold">/data</li>
                                <ul class="ml-4">
                                    <li class="font-bold gap-1 flex"><LucideFolder />/media</li>
                                    <ul class="ml-4">
                                        <li class="font-bold flex gap-1 items-center flex-wrap">
                                            <ProiconsLibrary />
                                            /library1
                                            <p class="font-normal text-xs leading-none dark:text-neutral-400">-</p>
                                            <p class="font-normal text-xs leading-none dark:text-neutral-400">Each library will show all of it's folders on the same page</p>
                                        </li>
                                        <ul class="ml-4">
                                            <li class="font-bold gap-1 flex"><LucideFolder /> /folder1</li>
                                            <ul class="ml-4">
                                                <li class="flex gap-1 items-center">
                                                    video1.mp4
                                                    <p class="text-xs leading-none dark:text-neutral-400">-</p>
                                                    <p class="text-xs leading-none dark:text-neutral-400">Each video will have it's own link by library/folder/?video=id</p>
                                                </li>
                                                <li>video2.mp4</li>
                                            </ul>
                                            <li class="font-bold gap-1 flex"><LucideFolder /> /folder2</li>
                                            <ul class="ml-4">
                                                <li>video1.mkv</li>
                                                <li>video2.mp4</li>
                                            </ul>
                                        </ul>
                                        <li class="font-bold flex gap-1"><ProiconsLibrary />/library2</li>
                                        <ul class="ml-4">
                                            <li class="font-bold gap-1 flex"><LucideFolder /> /folder1</li>
                                            <ul class="ml-4">
                                                <li>song1.wav</li>
                                                <li>song2.ogg</li>
                                            </ul>
                                            <li class="font-bold gap-1 flex"><LucideFolder />/folder2</li>
                                            <ul class="ml-4">
                                                <li>song1.mp3</li>
                                                <li>song2.mp3</li>
                                            </ul>
                                        </ul>
                                    </ul>
                                </ul>
                            </ul>
                        </div>
                    </li>
                    <li class="flex flex-wrap gap-1 items-center">
                        <p>
                            3. <RouterLink :class="`text-purple-500 underline decoration-1`" to="/dashboard/tasks">Start an "Index Files Task"</RouterLink> to discover all the
                            videos you have organised.
                        </p>
                        <p v-show="librariesAdded" class="ms-auto text-xs font-bold text-green-600 dark:text-green-700">Done!</p>
                    </li>
                    <li class="flex flex-wrap gap-1 items-center">
                        <p>
                            4. <RouterLink :class="`text-purple-500 underline decoration-1`" to="/dashboard/tasks">Start a "Verify Files Task"</RouterLink> to generate metadata for
                            all of your videos.
                        </p>
                        <p v-show="false" class="ms-auto text-xs font-bold text-green-600 dark:text-green-700">Done!</p>
                    </li>
                    <li>5. <RouterLink to="/">Enjoy</RouterLink> your media library!</li>
                </ul>
            </section>
        </template>
        <template v-slot:sidebar> </template>
    </LayoutBase>
</template>
