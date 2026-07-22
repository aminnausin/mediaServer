<script setup lang="ts">
import type { FolderResource, VideoResource } from '@/contracts/media';

import { groupActivityFeed, useActivityFeed } from '@/service/home/useActivityFeed';
import { TableLoadingSpinner } from '@/components/cedar-ui/table';
import { toPlural } from '@/service/util';
import { computed } from 'vue';
import { cn } from '@aminnausin/cedar-ui';

import SidebarHeader from '@/components/headers/SidebarHeader.vue';
import LazyImage from '@/components/lazy/LazyImage.vue';

import ProiconsMusicNote2 from '~icons/proicons/music-note-2';
import CircumPlay1 from '~icons/circum/play-1';
import IconFolder from '@/components/icons/IconFolder.vue';

const props = defineProps<{
    videos?: VideoResource[];
    music?: VideoResource[];
    updatedFolders?: FolderResource[];
    isLoading?: boolean;
}>();

const foldersRef = computed(() => props.updatedFolders);
const videosRef = computed(() => props.videos);
const musicRef = computed(() => props.music);

const feed = useActivityFeed(videosRef, musicRef, foldersRef, 12);
const groups = computed(() => groupActivityFeed(feed.value));

const typeIcon = (type: string) => (type === 'audio' ? ProiconsMusicNote2 : type === 'folder' ? IconFolder : CircumPlay1);
</script>

<template>
    <section class="flex flex-col gap-3">
        <SidebarHeader title="New content feed">
            <template #title> What's new </template>
        </SidebarHeader>

        <div v-if="isLoading" class="flex flex-col gap-1">
            <div v-for="n in 5" :key="n" class="flex h-20 animate-pulse items-center">
                <div class="h-20 w-22 shrink-0 rounded-l-md bg-neutral-300 dark:bg-neutral-950/80" />
                <div class="bg-surface-3 flex h-full flex-1 flex-col gap-1.5 rounded-r-md p-2">
                    <div class="h-3 w-3/4 rounded-md bg-neutral-300 dark:bg-neutral-700/60" />
                    <div class="h-3 w-1/3 rounded-md bg-neutral-200 dark:bg-neutral-700/40" />
                </div>
            </div>
        </div>

        <TableLoadingSpinner v-else-if="!feed.length" :is-loading="false" no-results-message="Nothing new yet" class="h-16 text-sm" />

        <div v-else v-for="group in groups" :key="group.label" class="flex flex-col gap-2">
            <div class="text-foreground-2 flex items-center justify-between gap-1 text-xs tracking-wide uppercase">
                <div>
                    {{ group.label }}
                </div>

                <div>{{ group.items.length }} Update{{ toPlural(group.items.length) }}</div>
            </div>
            <RouterLink
                v-for="item in group.items"
                :key="item.id"
                :to="item.url"
                :class="cn('group flex items-center', 'hover:bg-surface-1 dark:hover:bg-surface-2 duration-input data-card h-16 rounded shadow-sm transition-colors')"
            >
                <div class="relative flex h-full w-22 shrink-0 items-center overflow-hidden rounded-l-sm">
                    <template v-if="item.thumbnail">
                        <div
                            class="absolute inset-0 scale-120 blur-sm"
                            :style="{
                                backgroundImage: `url('${item.thumbnail}')`,
                                backgroundPosition: 'center',
                                backgroundSize: 'cover',
                                backgroundRepeat: 'no-repeat',
                            }"
                        ></div>

                        <LazyImage
                            :src="item.thumbnail"
                            :alt="item.title"
                            wrapper-class="absolute inset-0 flex items-center justify-center z-1"
                            :class="cn('aspect-video w-full object-cover', { 'aspect-square': item.type === 'audio' })"
                            loading="lazy"
                        />
                    </template>
                    <div v-else class="bg-surface-3 flex size-full items-center justify-center">
                        <component :is="typeIcon(item.type)" class="text-foreground-2 size-3.5" />
                    </div>
                    <span
                        v-if="item.isNew"
                        class="bg-primary/40 group-hover:bg-primary/60 3xl:text-[10px] absolute top-1 left-1 z-2 rounded-full px-1.5 py-0.5 text-[8px] leading-none text-white transition-colors"
                    >
                        NEW
                    </span>
                </div>
                <div class="flex h-full w-full items-center gap-3 overflow-hidden p-2 pe-3">
                    <div class="flex h-full min-w-0 flex-1 flex-col justify-between">
                        <p class="truncate text-sm">{{ item.title }}</p>
                        <p class="text-foreground-2 mt-0.5 truncate text-xs">{{ item.subtitle }}</p>
                    </div>
                    <component :is="typeIcon(item.type)" class="text-foreground-2 group-hover:text-foreground-0 size-3.5 shrink-0 transition-colors" />
                </div>
            </RouterLink>
        </div>
    </section>
</template>
