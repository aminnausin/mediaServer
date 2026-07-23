<script setup lang="ts">
import { useContinueWatching, useRecentlyAdded, useRecentlyReleased, useRecentlyUpdated, useRecentlyUploaded } from '@/service/home/useHomeQueries';
import { getScreenSizeRank, toFormattedDate, toTimeSpan } from '@/service/util';
import { breakpointsTailwind, useBreakpoints } from '@vueuse/core';
import { interleaveSpotlightItems } from '@/service/home/useSpotlightItems';
import { computed, onMounted } from 'vue';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';

import LayoutBase from '@/layouts/LayoutBase.vue';

import RecentlyUploadedCard from '@/components/cards/data/RecentlyUploadedCard.vue';
import RecentlyWatchedCard from '@/components/cards/data/RecentlyWatchedCard.vue';
import BrowseFolderCard from '@/components/cards/data/BrowseFolderCard.vue';
import HomeRightSidebar from '@/components/panels/HomeRightSidebar.vue';
import HomeSpotlight from '@/components/home/HomeSpotlight.vue';
import HomeShelf from '@/components/home/HomeShelf.vue';

const { pageTitle } = storeToRefs(useAppStore());

const { data: recentlyUploadedMusic, isLoading: isLoadingRecentlyUploadedMusic } = useRecentlyUploaded('audio');
const { data: recentlyUploaded, isLoading: isLoadingRecentlyUploaded } = useRecentlyUploaded('video');
const { data: continueWatching, isLoading: isLoadingContinueWatching } = useContinueWatching();
const { data: recentlyReleased, isLoading: isLoadingRecentlyReleased } = useRecentlyReleased();
const { data: recentlyUpdated, isLoading: isLoadingRecentlyUpdated } = useRecentlyUpdated();
const { data: recentlyAdded, isLoading: isLoadingRecentlyAdded } = useRecentlyAdded();

const breakpoints = useBreakpoints({ ...breakpointsTailwind, '3xl': 2000 });

const gridFolderCount = computed(() => {
    if (breakpoints.greaterOrEqual('3xl').value) return 8;
    if (breakpoints.greaterOrEqual('2xl').value) return 6;
    if (breakpoints.greaterOrEqual('sm').value) return 4;
    return 3;
});

const spotlightItems = computed(() =>
    interleaveSpotlightItems(
        [
            { items: recentlyUpdated.value ?? [], label: 'Just Updated' },
            { items: recentlyAdded.value ?? [], label: 'Just Added' },
            { items: recentlyReleased.value ?? [], label: 'Just Released' },
        ],
        4,
    ),
);

onMounted(() => {
    pageTitle.value = 'Explore';
    if (getScreenSizeRank() >= 3) useAppStore().cycleSideBar('feed', 'list-card');
});
</script>

<template>
    <LayoutBase>
        <template #content>
            <div id="content-home" class="page-height @container flex flex-col gap-8 text-sm">
                <HomeSpotlight v-if="spotlightItems.length" :items="spotlightItems" />

                <HomeShelf
                    v-if="isLoadingContinueWatching || !!continueWatching?.length"
                    title="Continue Watching"
                    skeleton-class="w-56 3xl:w-76 aspect-video"
                    :item-count="continueWatching?.length"
                    :is-loading="isLoadingContinueWatching"
                >
                    <RecentlyWatchedCard class="3xl:w-76" v-for="(media, index) in continueWatching" :key="media.id" :media="media" :eagerLoad="index < 2" />
                </HomeShelf>

                <HomeShelf
                    title="Recently Updated Folders"
                    skeleton-class="w-40 aspect-2-3"
                    :item-count="recentlyUpdated?.length"
                    :is-loading="isLoadingRecentlyUpdated"
                    :no-data-message="'Nothing released yet'"
                >
                    <BrowseFolderCard v-for="(folder, index) in recentlyUpdated" :key="folder.id" :folder="folder" :eagerLoad="index < 2">
                        <span v-if="folder.series?.updated_at" class="text-foreground-1 truncate"> Updated {{ toTimeSpan(folder.series?.updated_at, '') }} </span>
                    </BrowseFolderCard>
                </HomeShelf>

                <HomeShelf
                    title="Recently Added Series"
                    skeleton-class="w-40 aspect-2-3"
                    :item-count="recentlyAdded?.length"
                    :is-loading="isLoadingRecentlyAdded"
                    :no-data-message="'Nothing added yet'"
                    :use-grid="true"
                    :class="'3xl:grid-cols-8 xs:grid-cols-3 grid grid-cols-2 sm:grid-cols-4 2xl:grid-cols-6'"
                >
                    <BrowseFolderCard v-for="folder in recentlyAdded?.slice(0, gridFolderCount * 2)" :key="folder.id" :folder="folder" class="w-full">
                        <span v-if="folder.series?.created_at" class="text-foreground-1 truncate">
                            Added {{ toFormattedDate(folder.series?.created_at, false, { year: 'numeric', month: 'short', day: 'numeric' }) }}
                        </span>
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
                    v-if="isLoadingRecentlyUploaded || !!recentlyUploaded?.length"
                    title="Recently Uploaded Videos"
                    skeleton-class="w-56 3xl:w-76 aspect-video"
                    :item-count="recentlyUploaded?.length"
                    :is-loading="isLoadingRecentlyUploaded"
                >
                    <RecentlyUploadedCard class="3xl:w-76" v-for="media in recentlyUploaded" :key="media.id" :media="media" />
                </HomeShelf>

                <HomeShelf
                    v-if="isLoadingRecentlyUploadedMusic || !!recentlyUploadedMusic?.length"
                    title="Recently Uploaded Music"
                    skeleton-class="w-40 aspect-square"
                    :item-count="recentlyUploadedMusic?.length"
                    :is-loading="isLoadingRecentlyUploadedMusic"
                >
                    <RecentlyUploadedCard v-for="media in recentlyUploadedMusic" :key="media.id" :media="media" :force-audio="true" class="w-40" />
                </HomeShelf>
            </div>
        </template>
        <template v-slot:sidebar>
            <HomeRightSidebar />
        </template>
    </LayoutBase>
</template>
