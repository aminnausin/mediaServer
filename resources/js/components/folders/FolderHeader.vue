<script setup lang="ts">
import type { FolderResource } from '@/contracts/media';

import { handleStorageURL, toTimeSpan } from '@/service/util';
import { inject } from 'vue';

import FolderTab from '@/components/folders/FolderTab.vue';
import LazyImage from '@/components/lazy/LazyImage.vue';

const data = inject<FolderResource>('data');
</script>

<template>
    <FolderTab class="h-fit w-full gap-0 p-0">
        <div
            class="ring-r-default/5 flex h-52 items-end overflow-clip rounded-t-xl bg-cover text-white ring-1 lg:h-64"
            :style="{
                'background-image': `url(${data?.series?.banner_image?.path ?? 'https://s4.anilist.co/file/anilistcdn/user/banner/b6792701-mBLPRvzr3xPL.jpg'})`,
            }"
        >
            <div class="flex w-full flex-wrap items-end gap-4 bg-linear-to-b from-transparent to-neutral-950/40 p-3 text-center">
                <LazyImage
                    :wrapper-class="'relative h-fit w-fit'"
                    class="aspect-2-3 mx-auto w-24 min-w-24 rounded-md object-cover"
                    :src="data?.series?.poster_image?.path ?? handleStorageURL(data?.series?.thumbnail_url) ?? '/storage/thumbnails/default.webp'"
                    alt="profile"
                />
                <div class="text-centre flex flex-1 flex-wrap items-end justify-center gap-1 sm:pb-2">
                    <h2 class="w-full text-2xl text-balance capitalize sm:flex-1 sm:text-left">{{ data?.title }}</h2>
                    <p class="text-sm">Created: {{ toTimeSpan(data?.created_at || '', '') }}</p>
                </div>
            </div>
        </div>
        <slot> </slot>
    </FolderTab>
</template>
