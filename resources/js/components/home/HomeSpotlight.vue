<script setup lang="ts">
import type { SpotlightItem } from '@/service/home/useSpotlightItems';

import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { handleStorageURL } from '@/service/util';
import { ButtonBase } from '@/components/cedar-ui/button';
import { cn } from '@aminnausin/cedar-ui';

import PlayerOSDBase from '@/components/video/OSD/PlayerOSDBase.vue';
import LazyImage from '@/components/lazy/LazyImage.vue';

import ProiconsPlay from '~icons/proicons/play';
import IconPause from '@/components/icons/IconPause.vue';

const props = withDefaults(defineProps<{ items: SpotlightItem[]; intervalMs?: number }>(), { intervalMs: 6000 });

let timer: ReturnType<typeof setTimeout> | null = null;

let startedAt = 0;
let remaining = props.intervalMs;

const activeIndex = ref(0);
const isPaused = ref(false);

const activeItem = computed(() => props.items[activeIndex.value] ?? null);
const activeFolder = computed(() => activeItem.value?.folder ?? null);

const bannerSrc = computed(
    () =>
        activeFolder.value?.series?.banner_image?.path ??
        activeFolder.value?.series?.poster_image?.path ??
        handleStorageURL(activeFolder.value?.series?.thumbnail_url) ??
        '/storage/thumbnails/default.webp',
);

const activeUrl = computed(() => (activeFolder.value ? `/${activeFolder.value.category_id}/${activeFolder.value.id}/details` : '/'));

const nextFolder = () => {
    if (!props.items.length) return;
    activeIndex.value = (activeIndex.value + 1) % props.items.length;
};

function start() {
    if (timer || isPaused.value || props.items.length <= 1) return;

    startedAt = performance.now();

    timer = setTimeout(() => {
        remaining = props.intervalMs;
        nextFolder();
    }, remaining);
}

function pause() {
    if (!timer) return;

    clearTimeout(timer);
    timer = null;

    remaining -= performance.now() - startedAt;
}

function reset() {
    remaining = props.intervalMs;

    if (timer) {
        clearTimeout(timer);
        timer = null;
    }

    start();
}

const goTo = (index: number) => {
    activeIndex.value = index;
};

watch(isPaused, (paused) => (paused ? pause() : start()));
watch(activeIndex, reset);

onMounted(start);

onBeforeUnmount(() => timer && clearTimeout(timer));
</script>

<template>
    <div
        :class="cn('ring-r-default/5 group dark relative block h-[clamp(200px,28vw,380px)] w-full ring-1', 'content-auto rounded-xl [contain-intrinsic-size:auto_300px]')"
        @mouseenter="isPaused = true"
        @mouseleave="isPaused = false"
    >
        <Transition name="banner-fade">
            <div :key="activeFolder?.id" class="absolute inset-0">
                <LazyImage :src="bannerSrc" :alt="activeFolder?.title" class="size-full rounded-xl object-cover" loading="eager" decoding="async" />

                <div :class="cn('absolute inset-0 bg-linear-to-b from-transparent to-neutral-950/40 p-3 text-white', '@container flex')">
                    <div class="mt-auto flex h-fit w-full flex-wrap items-end justify-center gap-x-12 gap-y-2 @lg:flex-nowrap">
                        <RouterLink :class="cn('mt-auto flex h-fit flex-1 items-end gap-4 hover:text-white/90')" :to="activeUrl">
                            <LazyImage
                                alt="poster"
                                :class="cn('aspect-2-3 w-full max-w-24 rounded-md object-cover')"
                                :src="activeFolder?.series?.poster_image?.path ?? handleStorageURL(activeFolder?.series?.thumbnail_url) ?? '/storage/thumbnails/default.webp'"
                                :wrapper-class="cn('relative overflow-hidden origin-bottom-left shrink-0', 'w-16 @lg:w-24 opacity-100 ease-in')"
                            />

                            <div class="flex flex-col gap-0.5">
                                <span class="text-xs tracking-wide uppercase">{{ activeItem.label }}</span>
                                <h1 class="text-xl font-semibold capitalize md:text-2xl">{{ activeFolder?.title }}</h1>
                                <p v-if="activeFolder?.series?.description" class="hidden max-w-xl text-sm sm:line-clamp-2">
                                    {{ activeFolder.series.description }}
                                </p>
                            </div>
                        </RouterLink>
                        <div v-if="items.length > 1" class="flex h-fit max-w-52 min-w-40 flex-1 items-center">
                            <ButtonBase
                                v-for="(item, index) in items"
                                :key="item.folder.id"
                                type="button"
                                class="group/spotlight-nav block h-3 flex-1 px-0.5 py-1"
                                :aria-label="`Show ${item.folder.title}`"
                                @click="goTo(index)"
                            >
                                <div class="duration-input h-1 overflow-hidden rounded-full bg-white/25 transition-colors group-hover/spotlight-nav:bg-white/50">
                                    <div
                                        :class="
                                            cn('bg-primary group-hover/spotlight-nav:bg-primary-active h-full origin-left shadow', {
                                                'w-full': index < activeIndex,
                                                'w-0': index > activeIndex,
                                                'hero-fill': index === activeIndex,
                                            })
                                        "
                                        :style="index === activeIndex ? { animationDuration: `${intervalMs}ms`, animationPlayState: isPaused ? 'paused' : 'running' } : undefined"
                                    />
                                </div>
                            </ButtonBase>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <div class="absolute top-0 right-0 flex gap-1 p-3">
            <PlayerOSDBase
                :class="
                    cn('pointer-events-auto p-1 opacity-0 transition-opacity duration-300 ease-in', {
                        'opacity-100 duration-150 ease-out': isPaused,
                    })
                "
            >
                <ProiconsPlay v-if="isPaused" class="size-5" />
                <IconPause v-else class="size-5" />
            </PlayerOSDBase>
        </div>
    </div>
</template>
<style lang="css" scoped>
* {
    --banner-fade-duration: 0.7s;
}

.banner-fade-enter-active,
.banner-fade-leave-active {
    position: absolute;
    inset: 0;
    transition: opacity var(--banner-fade-duration) ease;
}

.banner-fade-enter-from,
.banner-fade-leave-to {
    opacity: 0;
}

.hero-fill {
    animation-name: fill;
    animation-timing-function: linear;
    animation-fill-mode: forwards;
}

@keyframes fill {
    0% {
        transform: scaleX(0);
    }
    100% {
        transform: scaleX(1);
    }
}
</style>
