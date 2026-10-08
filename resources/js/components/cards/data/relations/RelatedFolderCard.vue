<script setup lang="ts">
import type { FolderResource, SeriesRelation } from '@/contracts/media';

import { handleStorageURL, toTimeSpan } from '@/service/util';
import { FLAGS } from '@/config/featureFlags';
import { cn } from '@aminnausin/cedar-ui';

import PlayerOSDBase from '@/components/video/OSD/PlayerOSDBase.vue';
import BlurhashImage from '@/components/lazy/BlurhashImage.vue';
import LazyImage from '@/components/lazy/LazyImage.vue';

defineProps<{ folder: FolderResource; relation: SeriesRelation; eagerLoad?: boolean }>();

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
                'group data-card flex shrink-0 snap-start text-xs',
                'flex-col gap-2',
                'focus-within:outline-none',
                'content-auto [contain-intrinsic-size:120px_220px]',
                { 'rounded-none bg-transparent shadow-none': FLAGS.USE_TRANSPARENT_HOME_CARDS },
                $attrs.class,
            )
        "
        @focus="scrollIntoView"
    >
        <RouterLink :to="`/${folder.category_id}/${folder.id}/details/relations`" :class="cn('relative rounded-md shadow-sm [clip-path:inset(0_round_0.375rem)]')">
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
                <PlayerOSDBase class="absolute bottom-1 left-1 z-1 px-1.5 py-0.5">{{ relation.label }}</PlayerOSDBase>
            </slot>
        </RouterLink>
        <RouterLink
            :class="
                cn('focus-within:text-primary dark:focus-within:text-primary-muted flex w-full flex-col text-xs focus-visible:outline-none', {
                    'px-2 pb-2': !FLAGS.USE_TRANSPARENT_HOME_CARDS,
                })
            "
            :to="`/${folder.category_id}/${folder.id}`"
        >
            <p class="truncate group-focus-within:underline group-hover:underline">{{ folder.title }}</p>
            <span v-if="folder.series?.updated_at" class="text-foreground-1 truncate"> updated {{ toTimeSpan(folder.series?.updated_at, '', true) }} ago</span>
        </RouterLink>
    </div>
</template>
