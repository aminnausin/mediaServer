<script setup lang="ts">
import type { SwipeDirection } from '@aminnausin/cedar-ui';
import type { SpotlightItem } from '@/service/home/useSpotlightItems';

import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { handleStorageURL, toPlural } from '@/service/util';
import { cn, useSwipeHandler } from '@aminnausin/cedar-ui';
import { ButtonBase } from '@/components/cedar-ui/button';

import ButtonOverlay from '@/components/buttons/ButtonOverlay.vue';
import LazyImage from '@/components/lazy/LazyImage.vue';
import MediaTag from '@/components/labels/MediaTag.vue';

import ProiconsInfoSquare from '~icons/proicons/info-square';
import ProiconsPlay from '~icons/proicons/play';
import IconPause from '@/components/icons/IconPause.vue';

let timer: ReturnType<typeof setTimeout> | null = null;
let startedAt = 0;

const props = withDefaults(defineProps<{ items: SpotlightItem[]; intervalMs?: number; isLoading?: boolean }>(), { intervalMs: 6000 });

let remainingTime = props.intervalMs;

const activeIndex = ref(0);
const activeFolder = computed(() => activeItem.value?.folder ?? null);
const activeItem = computed(() => props.items[activeIndex.value] ?? null);

const activeUrl = computed(() => (activeFolder.value ? `/${activeFolder.value.category_id}/${activeFolder.value.id}/details` : '/'));

const hasMounted = ref(false);
const hasPaused = ref(false);
const isPaused = ref(false);

const bannerSrc = computed(
    () =>
        activeFolder.value?.series?.banner_image?.path ??
        activeFolder.value?.series?.poster_image?.path ??
        handleStorageURL(activeFolder.value?.series?.thumbnail_url) ??
        '/storage/thumbnails/default.webp',
);

const folderInfoTags = computed(() => {
    if (!activeFolder.value) return [];

    const getCount = (title: string, value: number = activeFolder.value.file_count) => `${value} ${title}${toPlural(value)}`;

    if (!activeFolder.value.series?.seasons) return [getCount('File')];
    if (activeFolder.value.is_majority_audio) return [getCount('Track')];

    const tags = [getCount('Episode', activeFolder.value.series.episodes)];

    if (activeFolder.value.series.seasons > 1) {
        tags.push(getCount('Season', activeFolder.value.series.seasons));
    }

    return tags;
});

const swipeDirections = ref<SwipeDirection[]>(['left', 'right']);
const leaveDirection = ref<string>();

const { offset, isSwiping, onPointerDown, onPointerMove, onPointerUp } = useSwipeHandler({
    directions: swipeDirections,
    swipeThreshold: { px: 45 },
    onSwipeOut,
});

function onSwipeOut() {
    if (Math.abs(offset.value.x) < 45) return;

    leaveDirection.value = offset.value.x > 0 ? '100%' : '-100%';
    (offset.value.x > 0 ? nextFolder : previousFolder)();
}

const nextFolder = () => {
    if (!props.items.length) return;
    activeIndex.value = (activeIndex.value + 1) % props.items.length;
};

const previousFolder = () => {
    if (!props.items.length) return;
    activeIndex.value = activeIndex.value === 0 ? props.items.length - 1 : (activeIndex.value - 1) % props.items.length;
};

function startTimer() {
    if (timer || isPaused.value || props.items.length <= 1) return;

    startedAt = performance.now();

    timer = setTimeout(() => {
        remainingTime = props.intervalMs;
        nextFolder();
    }, remainingTime);
}

function pauseTimer() {
    if (!timer) return;

    clearTimeout(timer);
    timer = null;

    remainingTime -= performance.now() - startedAt;
}

function resetTimer() {
    remainingTime = props.intervalMs;

    if (timer) {
        clearTimeout(timer);
        timer = null;
    }

    startTimer();
}

const goTo = (index: number) => {
    activeIndex.value = index;
};

watch(isPaused, (paused) => (paused ? pauseTimer() : startTimer()));
watch([activeIndex, () => props.items.length], () => {
    if (hasMounted.value) resetTimer();
});

onMounted(() => {
    hasMounted.value = true;
    resetTimer();
});
onBeforeUnmount(() => timer && clearTimeout(timer));
</script>

<template>
    <div
        v-if="isLoading"
        class="ring-r-default/5 content-auto bg-foreground-3/50 dark:bg-surface-2 @container flex h-80 w-full animate-pulse items-end gap-4 rounded-xl p-3 ring-1 [contain-intrinsic-size:auto_300px] sm:h-[clamp(200px,28vw,380px)]"
    >
        <div class="mt-auto flex h-fit w-full flex-col flex-wrap items-center justify-center gap-x-4 gap-y-2 @md:flex-row @md:items-end @lg:flex-nowrap @lg:gap-x-12">
            <div class="mt-auto flex h-fit flex-1 flex-col items-center gap-4 hover:text-white/90 @md:flex-row @md:items-end">
                <div class="aspect-2-3 bg-foreground-3 3xl:w-36 w-32 rounded-md sm:w-24 2xl:w-30"></div>
                <div class="flex flex-col items-center gap-1 @md:items-start">
                    <div class="bg-foreground-3 h-5 w-32 rounded-md"></div>
                    <div class="bg-foreground-3/80 h-6 w-40 rounded-lg"></div>
                </div>
            </div>
            <div class="flex h-fit w-full max-w-2/3 min-w-40 flex-1 items-center @md:w-auto @md:max-w-52" role="tablist">
                <div v-for="i in 8" role="tab" type="button" class="group/spotlight-nav block h-3 flex-1 px-0.5 py-1" :key="i">
                    <div class="duration-input bg-foreground-3 h-1 overflow-hidden rounded-full transition-colors group-hover/spotlight-nav:bg-white/50"></div>
                </div>
            </div>
        </div>
    </div>
    <div
        v-else
        :class="cn('ring-r-default/5 group relative block h-96 w-full ring-1 sm:h-[clamp(200px,28vw,380px)]', 'content-auto rounded-xl [contain-intrinsic-size:auto_300px]')"
        @mouseenter="isPaused = true"
        @mouseleave="if (!hasPaused) isPaused = false;"
        @pointerdown="onPointerDown"
        @pointermove="onPointerMove"
        @pointerup="onPointerUp"
        @pointercancel="onPointerUp"
    >
        <Transition :name="hasMounted ? 'banner-fade' : ''">
            <div :key="activeFolder?.id" class="absolute inset-0 -z-10">
                <LazyImage loading="eager" decoding="async" fetch-priority="high" class="size-full rounded-xl object-cover" :alt="activeFolder?.title" :src="bannerSrc" />
            </div>
        </Transition>

        <div :class="cn('size-full bg-linear-to-b from-transparent to-neutral-950/40 text-white', '@container relative flex p-3')">
            <div class="flex w-full flex-1 flex-col items-center justify-center gap-x-4 gap-y-2 @md:flex-row @md:items-end @lg:flex-nowrap @lg:gap-x-12">
                <div :class="cn('swipe relative flex w-full flex-1 @md:w-auto')" :style="{ '--offset-x': `${0}px` }" :data-swiping="isSwiping">
                    <Transition
                        :name="hasMounted ? 'banner-fade' : ''"
                        @after-leave="leaveDirection = '0%'"
                        @before-leave="(el) => (el as HTMLElement).style.setProperty('--leave-direction', leaveDirection ?? '0%')"
                    >
                        <div
                            :key="activeFolder?.id"
                            :style="{ '--leave-direction': leaveDirection }"
                            class="flex size-full flex-col items-center justify-end gap-3 @md:flex-row @md:items-end @md:justify-start"
                        >
                            <RouterLink :to="activeUrl" class="peer" title="View folder details">
                                <LazyImage
                                    :alt="`${activeFolder.title} poster`"
                                    :class="cn('aspect-2-3 duration-input w-full rounded-lg object-cover shadow-sm transition-shadow')"
                                    :src="activeFolder?.series?.poster_image?.path ?? handleStorageURL(activeFolder?.series?.thumbnail_url) ?? '/storage/thumbnails/default.webp'"
                                    :wrapper-class="cn('shrink-0 relative', 'w-32 sm:w-24 2xl:w-30 3xl:w-36 opacity-100 ease-in h-fit select-none ')"
                                />
                            </RouterLink>

                            <RouterLink
                                :aria-label="`View folder details for ${activeFolder.title}`"
                                class="group/spotlight-link flex flex-col gap-0.5 text-center @md:text-start peer-hover:[&_h1]:underline"
                                :to="activeUrl"
                            >
                                <span class="text-xs tracking-wide whitespace-nowrap uppercase">{{ activeItem.label }}</span>

                                <h1 class="line-clamp-2 text-xl font-semibold text-balance capitalize group-hover/spotlight-link:underline md:text-2xl">
                                    {{ activeFolder?.title }}
                                </h1>

                                <p v-if="activeFolder?.series?.description" class="xs:line-clamp-1 hidden max-w-xl text-sm text-pretty sm:line-clamp-2">
                                    {{ activeFolder.series.description }}
                                </p>
                                <div class="@md:-mx-1: mt-1 flex h-5 w-full flex-wrap justify-center gap-1 overflow-clip [overflow-clip-margin:4px] @md:justify-start">
                                    <MediaTag v-for="tag in folderInfoTags" :key="tag" class="bg-surface-2! text-foreground-0! py-0.5 text-xs transition-none dark:bg-neutral-900!">
                                        {{ tag }}
                                    </MediaTag>
                                    <template v-if="activeFolder.series?.folder_tags?.length">
                                        <MediaTag
                                            v-for="tag in [...activeFolder.series.folder_tags.slice(0, Math.min(3, activeFolder.series.folder_tags.length))]"
                                            :key="tag.id"
                                            class="bg-surface-2! text-foreground-0! py-0.5 text-xs transition-none dark:bg-neutral-900!"
                                        >
                                            {{ tag.name }}
                                        </MediaTag>
                                    </template>
                                </div>
                                <div class="mt-1 hidden! h-8 @md:block"></div>
                            </RouterLink>
                        </div>
                    </Transition>
                </div>
                <div v-if="items.length > 1" class="mx-auto flex h-fit w-full max-w-2/3 min-w-40 items-center @md:mx-0 @md:max-w-52" role="tablist">
                    <ButtonBase
                        v-for="(item, index) in items"
                        role="tab"
                        type="button"
                        class="group/spotlight-nav block h-3 flex-1 px-0.5 py-1"
                        :key="item.folder.id"
                        :title="`Show spotilight item ${index + 1} of ${items.length}`"
                        :aria-current="index === activeIndex ? 'true' : undefined"
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
            <div class="dark absolute bottom-0 left-0 hidden! gap-3 p-3 @md:flex">
                <div class="3xl:w-36 w-32 sm:w-24 2xl:w-30"></div>
                <div class="flex gap-2">
                    <ButtonBase
                        class="text-foreground-i h-auto min-h-6 gap-1 bg-white ring-white hover:bg-neutral-100"
                        title="Play"
                        :to="`/${activeFolder.category_id}/${activeFolder.id}`"
                    >
                        <ProiconsPlay class="size-4" />
                        <span class="leading-none">View</span>
                    </ButtonBase>
                    <ButtonBase class="text-foreground-0 size-8 border border-neutral-700/10 bg-neutral-900/60 p-0" :to="activeUrl">
                        <ProiconsInfoSquare class="size-5" />
                    </ButtonBase>
                </div>
            </div>
        </div>

        <div class="absolute top-0 right-0 flex gap-1 p-3">
            <ButtonOverlay
                :class="
                    cn('size-7', 'hocus:opacity-100 hocus:ease-out opacity-0 ease-in', 'outline outline-transparent focus-visible:outline-white', {
                        'opacity-60 ease-out': isPaused,
                    })
                "
                :title="isPaused ? 'Unpause' : 'Pause'"
                :aria-label="`${isPaused ? 'Unpause' : 'Pause'} spotlight`"
                @click="
                    isPaused = !isPaused;
                    hasPaused = isPaused;
                "
            >
                <ProiconsPlay v-if="isPaused" class="size-5" />
                <IconPause v-else class="size-5" />
            </ButtonOverlay>
        </div>
    </div>
</template>
<style lang="css" scoped>
.swipe {
    transform: translateX(var(--offset-x, 0px));
    transition: transform 100ms ease;
    touch-action: pan-y;
    user-select: none;
}

.swipe[data-swiping='true'] {
    transition: none;
    will-change: transform;
}

.banner-fade-enter-active,
.banner-fade-leave-active {
    position: absolute;
    inset: 0;
    transition:
        opacity 0.7s ease,
        transform 1s ease;
}

.banner-fade-enter-from {
    opacity: 0;
}

.banner-fade-leave-from {
    transform: translateX(var(--offset-x, 0%));
    opacity: 1;
}

.banner-fade-leave-to {
    transform: translateX(var(--leave-direction, 0%));
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
