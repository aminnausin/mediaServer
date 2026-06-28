<script setup lang="ts">
import { handleStorageURL, toFormattedDate, toTimeSpan } from '@/service/util';
import { useContentStore } from '@/stores/ContentStore';
import { storeToRefs } from 'pinia';

import ProIconsPhotoOff from '@/components/icons/ProIconsPhotoOff.vue';
import FolderTab from '@/components/folders/FolderTab.vue';
import LazyImage from '@/components/lazy/LazyImage.vue';

const { stateFolder, isLoadingContent } = storeToRefs(useContentStore());
</script>

<template>
    <FolderTab class="h-fit w-full gap-0 p-0">
        <div
            class="ring-r-default/5 flex min-h-52 items-end overflow-clip rounded-t-xl bg-cover text-white ring-1 lg:h-64"
            :style="{
                'background-image': `url(${stateFolder.series?.banner_image?.path ?? 'https://s4.anilist.co/file/anilistcdn/user/banner/b6792701-mBLPRvzr3xPL.jpg'})`,
            }"
        >
            <div v-if="!isLoadingContent && stateFolder.id" class="flex w-full flex-wrap items-end gap-4 bg-linear-to-b from-transparent to-neutral-950/40 p-3 text-center">
                <LazyImage
                    :wrapper-class="'relative h-fit w-fit'"
                    class="aspect-2-3 mx-auto w-24 min-w-24 rounded-md object-cover"
                    :src="stateFolder?.series?.poster_image?.path ?? handleStorageURL(stateFolder?.series?.thumbnail_url) ?? '/storage/thumbnails/default.webp'"
                    alt="profile"
                />
                <div class="text-centre flex flex-1 flex-wrap items-end justify-center gap-1 sm:pb-2">
                    <h2 class="w-full text-2xl text-balance capitalize sm:flex-1 sm:text-left">{{ stateFolder.title }}</h2>
                    <p v-if="stateFolder.created_at" class="text-sm" :title="toFormattedDate(stateFolder.created_at)">Created: {{ toTimeSpan(stateFolder.created_at, '') }}</p>
                </div>
            </div>
            <div v-else class="flex w-full flex-wrap items-end gap-4 bg-linear-to-b from-transparent to-neutral-950/40 p-3 text-center">
                <div class="aspect-2-3 bg-surface-3 mx-auto flex w-24 min-w-24 items-center justify-center rounded-md">
                    <ProIconsPhotoOff class="text-foreground-1 dark:text-foreground-0 size-6 animate-pulse" />
                </div>

                <div class="text-centre flex flex-1 animate-pulse flex-wrap items-end justify-center gap-1 sm:justify-between sm:pb-2">
                    <div class="xs:mx-10 h-8 w-full rounded bg-white/80 text-2xl text-balance capitalize sm:mx-0 sm:max-w-1/3 sm:flex-1 sm:text-left"></div>
                    <div class="h-5 w-20 rounded bg-neutral-200/80 sm:w-12"></div>
                </div>
            </div>
        </div>
        <slot> </slot>
    </FolderTab>
</template>
