<script setup lang="ts">
import type { FolderResource } from '@/contracts/media';

import { handleStorageURL, toPlural } from '@/service/util';
import { FLAGS } from '@/config/featureFlags';
import { cn } from '@aminnausin/cedar-ui';

import PlayerOSDBase from '@/components/video/OSD/PlayerOSDBase.vue';
import BlurhashImage from '@/components/lazy/BlurhashImage.vue';
import LazyImage from '@/components/lazy/LazyImage.vue';

import IconFolder from '@/components/icons/IconFolder.vue';

defineProps<{ folder: FolderResource; eagerLoad?: boolean }>();

const scrollIntoView = (e: FocusEvent) => {
    (e.currentTarget as HTMLElement).scrollIntoView({
        inline: 'nearest',
        block: 'nearest',
    });
};
</script>
<template>
    <div
        :class="
            cn(
                'group data-card flex w-40 shrink-0 snap-start flex-col gap-2 rounded-md',
                'focus-within:outline-none',
                'content-auto [contain-intrinsic-size:160px_280px]',
                { 'rounded-none bg-transparent shadow-none': FLAGS.USE_TRANSPARENT_HOME_CARDS },
                $attrs.class,
            )
        "
        @focus="scrollIntoView"
    >
        <RouterLink
            :to="`/${folder.category_id}/${folder.id}/details`"
            :class="cn('relative overflow-clip rounded-t-md shadow-sm', { 'rounded-b-md': FLAGS.USE_TRANSPARENT_HOME_CARDS })"
        >
            <component
                :is="folder.series?.poster_image?.blur_hash ? BlurhashImage : LazyImage"
                :src="folder.series?.poster_image?.path ?? handleStorageURL(folder.series?.thumbnail_url) ?? '/storage/thumbnails/default.webp'"
                :class="'aspect-2-3 w-full object-cover'"
                :alt="folder.title"
                :fetch-priority="eagerLoad ? 'high' : 'auto'"
                :loading="eagerLoad ? 'eager' : 'lazy'"
                :blurhash="folder.series?.poster_image?.blur_hash"
            />
            <slot name="overlay">
                <PlayerOSDBase
                    class="absolute bottom-1 left-1 z-2 flex h-6 min-w-6 items-center justify-center p-0 px-0.5 text-[10px] tabular-nums"
                    :aria-label="`${folder.file_count} file${toPlural(folder.file_count)}`"
                >
                    {{ folder.file_count }}
                </PlayerOSDBase>

                <div :class="cn('absolute right-0 bottom-0 z-2 size-7')">
                    <div
                        :class="
                            cn(
                                'pointer-events-auto size-6 opacity-0',
                                'origin-center scale-80',
                                'duration-input transition-[opacity,scale]',
                                'group-hover:scale-100 group-hover:opacity-100 hover:scale-110 active:scale-95',
                                'group-focus-within:scale-100 group-focus-within:opacity-100',
                            )
                        "
                    >
                        <PlayerOSDBase
                            class="group-focus-within:text-primary-muted duration-input size-full stroke-[0.5] p-0 transition-[color,stroke-width] group-focus-within:stroke-1"
                        >
                            <IconFolder class="size-4" stroke-width="current" />
                        </PlayerOSDBase>
                    </div>
                </div>
            </slot>
        </RouterLink>
        <RouterLink :class="cn('flex w-full flex-col text-xs', { 'px-2 pb-2': !FLAGS.USE_TRANSPARENT_HOME_CARDS })" :to="`/${folder.category_id}/${folder.id}`">
            <slot name="title">
                <p class="truncate group-hover:underline">{{ folder.title }}</p>
            </slot>
            <slot />
        </RouterLink>
    </div>
</template>
