<script setup lang="ts">
import type { FolderResource, VideoResource } from '@/contracts/media';

import { handleStorageURL } from '@/service/util';
import { ButtonBase } from '@/components/cedar-ui/button';
import { computed } from 'vue';
import { cn } from '@aminnausin/cedar-ui';

import LazyImage from '@/components/lazy/LazyImage.vue';

import CircumPlay1 from '~icons/circum/play-1';

type HeroItem = { type: 'folder'; data: FolderResource } | { type: 'video'; data: VideoResource };

const props = defineProps<{ item: HeroItem }>();

const isFolder = computed(() => props.item.type === 'folder');
const folder = computed(() => (isFolder.value ? (props.item.data as FolderResource) : null));
const video = computed(() => (!isFolder.value ? (props.item.data as VideoResource) : null));

const title = computed(() => folder.value?.title ?? video.value?.title ?? video.value?.name);
const eyebrow = computed(() => (isFolder.value ? 'New series' : 'Just uploaded'));
const description = computed(() => folder.value?.series?.description ?? video.value?.description);

const bannerSrc = computed(
    () =>
        folder.value?.series?.banner_image?.path ??
        folder.value?.series?.poster_image?.path ??
        handleStorageURL(folder.value?.series?.thumbnail_url) ??
        video.value?.metadata?.poster_image?.path ??
        '/storage/thumbnails/default.webp',
);

const linkTo = computed(() => (folder.value ? `/${folder.value.category_id}/${folder.value.id}/details` : (video.value?.url ?? '/')));
</script>

<template>
    <RouterLink :to="linkTo" :class="cn('group relative block h-[clamp(200px,28vw,380px)] w-full overflow-hidden rounded-xl', 'content-auto [contain-intrinsic-size:auto_300px]')">
        <LazyImage
            :src="bannerSrc"
            :alt="title"
            class="size-full object-cover transition-transform duration-700 ease-out group-hover:scale-105"
            fetch-priority="high"
            loading="eager"
        />

        <div class="absolute inset-0 flex w-full flex-wrap items-end gap-4 bg-linear-to-b from-transparent to-neutral-950/40 p-5 text-white md:p-6">
            <div class="flex flex-col gap-2">
                <span class="text-xs tracking-wide uppercase">{{ eyebrow }}</span>
                <h1 class="text-xl font-semibold md:text-2xl">{{ title }}</h1>
                <p v-if="description" class="line-clamp-2 max-w-xl text-sm">{{ description }}</p>
                <ButtonBase class="text-foreground-0 dark:text-foreground-i mt-1 w-fit gap-2 bg-white">
                    <CircumPlay1 class="size-4" /> {{ isFolder ? 'View' : 'Play' }}
                </ButtonBase>
            </div>

            <div class="bg-surface-2/30 ms-auto h-1.5 w-32 overflow-clip rounded-full">
                <div class="bg-primary loading-bar h-full w-full origin-left"></div>
            </div>
        </div>
    </RouterLink>
</template>
<style lang="css" scoped>
.loading-bar {
    animation: load 5s linear infinite;
}

@keyframes load {
    0% {
        transform: scaleX(0);
    }
    100% {
        transform: scaleX(1);
    }
}
</style>
