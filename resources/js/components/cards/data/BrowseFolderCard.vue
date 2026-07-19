<script setup lang="ts">
import type { FolderResource } from '@/contracts/media';

import { handleStorageURL } from '@/service/util';
import { FLAGS } from '@/config/featureFlags';
import { cn } from '@aminnausin/cedar-ui';

import PlayerOSDBase from '@/components/video/OSD/PlayerOSDBase.vue';
import LazyImage from '@/components/lazy/LazyImage.vue';

import IconFolder from '@/components/icons/IconFolder.vue';

defineProps<{ folder: FolderResource }>();
</script>
<template>
    <RouterLink
        :to="`/${folder.category_id}/${folder.id}/details`"
        :class="
            cn(
                'group data-card flex w-40 shrink-0 snap-start flex-col gap-2 rounded-md',
                'content-auto [contain-intrinsic-size:160px_240px]',
                { 'rounded-none bg-transparent shadow-none': FLAGS.USE_TRANSPARENT_HOME_CARDS },
                $attrs.class,
            )
        "
    >
        <div :class="cn('relative overflow-clip rounded-t-md shadow-sm', { 'rounded-b-md': FLAGS.USE_TRANSPARENT_HOME_CARDS })">
            <LazyImage
                :src="folder.series?.poster_image?.path ?? handleStorageURL(folder.series?.thumbnail_url) ?? '/storage/thumbnails/default.webp'"
                :class="'aspect-2-3 w-full object-cover'"
                :alt="folder.title"
            />
            <slot name="overlay">
                <PlayerOSDBase class="absolute bottom-1 left-1 h-6 min-w-6 p-0 text-[10px] tabular-nums">
                    {{ folder.file_count }}
                </PlayerOSDBase>

                <div :class="cn('absolute right-0 bottom-0 size-7')">
                    <div
                        :class="
                            cn(
                                'pointer-events-auto size-6 p-0 opacity-0',
                                'origin-center scale-80 transform-gpu will-change-transform',
                                'duration-input transition-[opacity,scale] group-hover:scale-100 group-hover:opacity-100 hover:scale-110 active:scale-95',
                            )
                        "
                    >
                        <PlayerOSDBase class="size-full p-0">
                            <IconFolder class="size-4" />
                        </PlayerOSDBase>
                    </div>
                </div>
            </slot>
        </div>
        <div :class="cn('flex w-full flex-col px-2 pb-2 text-xs', { 'p-0': FLAGS.USE_TRANSPARENT_HOME_CARDS })">
            <slot name="title">
                <p class="truncate">{{ folder.title }}</p>
            </slot>
            <slot />
        </div>
    </RouterLink>
</template>
