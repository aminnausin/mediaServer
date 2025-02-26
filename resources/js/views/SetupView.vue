<script setup lang="ts">
import { useAuthStore } from '@/stores/AuthStore';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { onMounted } from 'vue';

import LayoutBase from '@/layouts/LayoutBase.vue';

import LucideFolder from '~icons/lucide/folder';

const { pageTitle, selectedSideBar } = storeToRefs(useAppStore());
const { auth } = useAuthStore();

onMounted(() => {
    pageTitle.value = 'MediaServer Setup';
    selectedSideBar.value = '';
});
</script>

<template>
    <LayoutBase>
        <template v-slot:content>
            <section id="content-settings" class="flex flex-col gap-4 [&>*]:space-y-1 space-y-2 min-h-[80vh]">
                <h2>Before you can start watching your media, you must complete these setup steps</h2>
                <ul class="text-sm flex flex-col gap-2">
                    <li class="flex flex-wrap gap-1 items-center">
                        <p class="mr-auto">
                            1. <RouterLink to="/register" :class="`text-purple-500 underline decoration-1`">Register</RouterLink> for an account. This will be your administrator
                            account.
                        </p>
                        <p v-show="auth" class="text-xs text-green-700">Done!</p>
                    </li>
                    <li>
                        <span class="flex gap-1 items-start">
                            <p>2. Put your files in the data/media folder. You must follow this folder structure. An example url would be /category1/folder2</p>
                            <p v-show="false" class="text-xs text-green-700">Done!</p>
                        </span>
                        <div class="p-2 font-mono w-full">
                            <ul>
                                <li class="font-bold">data</li>
                                <ul class="ml-4">
                                    <li class="font-bold">media</li>
                                    <ul class="ml-4">
                                        <li class="font-bold flex gap-1 items-center">
                                            category1
                                            <p class="font-normal text-xs">- Each category will show all of it's folders on the same page</p>
                                        </li>
                                        <ul class="ml-4">
                                            <li class="font-bold">folder1</li>
                                            <ul class="ml-4">
                                                <li class="flex gap-1 items-center">
                                                    video1.mp4
                                                    <p class="text-xs">- Each video will have it's own link by category/folder/?video=id</p>
                                                </li>
                                                <li>video2.mp4</li>
                                            </ul>
                                            <li class="font-bold">folder2</li>
                                            <ul class="ml-4">
                                                <li>video1.mp4</li>
                                                <li>video2.mp4</li>
                                            </ul>
                                        </ul>
                                        <li class="font-bold flex gap-1">category2</li>
                                        <ul class="ml-4">
                                            <li class="font-bold">folder1</li>
                                            <ul class="ml-4">
                                                <li>video1.mp4</li>
                                                <li>video2.mp4</li>
                                            </ul>
                                            <li class="font-bold">folder2</li>
                                            <ul class="ml-4">
                                                <li>video1.mp4</li>
                                                <li>video2.mp4</li>
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
                        <p v-show="false" class="text-xs text-green-700">Done!</p>
                    </li>
                    <li class="flex flex-wrap gap-1 items-center">
                        <p>
                            4. <RouterLink :class="`text-purple-500 underline decoration-1`" to="/dashboard/tasks">Start a "Verify Files Task"</RouterLink> to generate metadata for
                            all of your videos.
                        </p>
                        <p v-show="false" class="text-xs text-green-700">Done!</p>
                    </li>
                    <li>5. <RouterLink to="/">Enjoy</RouterLink> your media library!</li>
                </ul>
            </section>
        </template>
        <template v-slot:sidebar>
            <!-- <Settings /> -->
        </template>
    </LayoutBase>
</template>
