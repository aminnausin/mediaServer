<script setup lang="ts">
import { useContinueWatching, useRecentlyAdded, useRecentlyReleased, useRecentlyUpdated, useRecentlyUploaded } from '@/service/home/useHomeQueries';
import { toFormattedDate, toTimeSpan } from '@/service/util';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { onMounted } from 'vue';

import LayoutBase from '@/layouts/LayoutBase.vue';

import RecentlyUploadedCard from '@/components/cards/data/RecentlyUploadedCard.vue';
import RecentlyWatchedCard from '@/components/cards/data/RecentlyWatchedCard.vue';
import BrowseFolderCard from '@/components/cards/data/BrowseFolderCard.vue';
import HomeShelf from '@/components/home/HomeShelf.vue';

const { pageTitle, selectedSideBar } = storeToRefs(useAppStore());

const { data: recentlyUploadedMusic, isLoading: isLoadingRecentlyUploadedMusic } = useRecentlyUploaded('audio');
const { data: recentlyUploaded, isLoading: isLoadingRecentlyUploaded } = useRecentlyUploaded('video');
const { data: continueWatching, isLoading: isLoadingContinueWatching } = useContinueWatching();
const { data: recentlyReleased, isLoading: isLoadingRecentlyReleased } = useRecentlyReleased();
const { data: recentlyUpdated, isLoading: isLoadingRecentlyUpdated } = useRecentlyUpdated();
const { data: recentlyAdded, isLoading: isLoadingRecentlyAdded } = useRecentlyAdded();

onMounted(() => {
    pageTitle.value = 'Explore';
    selectedSideBar.value = '';
});
</script>

<template>
    <LayoutBase>
        <template #content>
            <div id="content-home" class="page-height @container flex flex-col gap-6 text-sm">
                <HomeShelf
                    v-if="isLoadingContinueWatching || !!continueWatching?.length"
                    title="Continue Watching"
                    skeleton-class="w-56 3xl:w-76 aspect-video"
                    :item-count="continueWatching?.length"
                    :is-loading="isLoadingContinueWatching"
                >
                    <RecentlyWatchedCard class="3xl:w-76" v-for="video in continueWatching" :key="video.id" :video="video" />
                </HomeShelf>

                <HomeShelf
                    title="Recently Updated Folders"
                    skeleton-class="w-40 aspect-2-3"
                    :item-count="recentlyUpdated?.length"
                    :is-loading="isLoadingRecentlyUpdated"
                    :no-data-message="'Nothing released yet'"
                >
                    <BrowseFolderCard v-for="folder in recentlyUpdated" :key="folder.id" :folder="folder">
                        <span v-if="folder.series?.updated_at" class="text-foreground-1 truncate"> Updated {{ toTimeSpan(folder.series?.updated_at, '') }} </span>
                    </BrowseFolderCard>
                </HomeShelf>

                <HomeShelf
                    title="Recently Released Series"
                    skeleton-class="w-40 aspect-2-3"
                    :item-count="recentlyReleased?.length"
                    :is-loading="isLoadingRecentlyReleased"
                    :no-data-message="'Nothing released yet'"
                >
                    <BrowseFolderCard v-for="folder in recentlyReleased" :key="folder.id" :folder="folder">
                        <span v-if="folder.series?.started_at" class="text-foreground-1 truncate">
                            {{ toFormattedDate(folder.series?.started_at, false, { year: 'numeric', month: 'long' }) }}
                        </span>
                    </BrowseFolderCard>
                </HomeShelf>

                <HomeShelf
                    title="Recently Added Folders"
                    skeleton-class="w-40 aspect-2-3"
                    :item-count="recentlyAdded?.length"
                    :is-loading="isLoadingRecentlyAdded"
                    :no-data-message="'Nothing added yet'"
                >
                    <BrowseFolderCard v-for="folder in recentlyAdded" :key="folder.id" :folder="folder">
                        <span v-if="folder.series?.created_at" class="text-foreground-1 truncate"> Added {{ toTimeSpan(folder.series?.created_at, '') }} </span>
                    </BrowseFolderCard>
                </HomeShelf>

                <HomeShelf
                    v-if="isLoadingRecentlyUploaded || !!recentlyUploaded?.length"
                    title="Recently Uploaded Videos"
                    skeleton-class="w-56 3xl:w-76 aspect-video"
                    :item-count="recentlyUploaded?.length"
                    :is-loading="isLoadingRecentlyUploaded"
                >
                    <RecentlyUploadedCard class="3xl:w-76" v-for="video in recentlyUploaded" :key="video.id" :video="video" />
                </HomeShelf>

                <HomeShelf
                    v-if="isLoadingRecentlyUploadedMusic || !!recentlyUploadedMusic?.length"
                    title="Recently Uploaded Music"
                    skeleton-class="w-40 aspect-square"
                    :item-count="recentlyUploadedMusic?.length"
                    :is-loading="isLoadingRecentlyUploadedMusic"
                >
                    <RecentlyUploadedCard v-for="video in recentlyUploadedMusic" :key="video.id" :video="video" :force-audio="true" class="w-40" />
                </HomeShelf>
            </div>
        </template>
    </LayoutBase>
</template>
