<script setup lang="ts">
import type { SpotlightItem } from '@/service/home/useSpotlightItems';

import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { handleStorageURL } from '@/service/util';
import { ButtonBase } from '@/components/cedar-ui/button';
import { cn } from '@aminnausin/cedar-ui';

import ButtonOverlay from '@/components/buttons/ButtonOverlay.vue';
import LazyImage from '@/components/lazy/LazyImage.vue';
import MediaTag from '@/components/labels/MediaTag.vue';

import ProiconsInfoSquare from '~icons/proicons/info-square';
import ProiconsPlay from '~icons/proicons/play';
import IconPause from '@/components/icons/IconPause.vue';

const props = withDefaults(defineProps<{ items: SpotlightItem[]; intervalMs?: number; isLoading?: boolean }>(), { intervalMs: 6000 });

let timer: ReturnType<typeof setTimeout> | null = null;

let startedAt = 0;
let remaining = props.intervalMs;

const activeIndex = ref(0);
const isPaused = ref(false);
const hasMounted = ref(false);
const hasPaused = ref(false);

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
watch([activeIndex, () => props.items.length], () => {
    if (hasMounted.value) reset();
});

onMounted(() => {
    hasMounted.value = true;
    reset();
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
        :class="cn('ring-r-default/5 group dark relative block h-88 w-full ring-1 sm:h-[clamp(200px,28vw,380px)]', 'content-auto rounded-xl [contain-intrinsic-size:auto_300px]')"
        @mouseenter="isPaused = true"
        @mouseleave="if (!hasPaused) isPaused = false;"
    >
        <Transition :name="hasMounted ? 'banner-fade' : ''">
            <div :key="activeFolder?.id" class="absolute inset-0 -z-10">
                <LazyImage loading="eager" decoding="async" fetch-priority="high" class="size-full rounded-xl object-cover" :alt="activeFolder?.title" :src="bannerSrc" />
            </div>
        </Transition>

        <div :class="cn('size-full bg-linear-to-b from-transparent to-neutral-950/40 text-white', '@container relative flex p-3')">
            <div class="flex w-full flex-1 flex-col items-center justify-center gap-x-4 gap-y-2 @md:flex-row @md:items-end @lg:flex-nowrap @lg:gap-x-12">
                <RouterLink :class="cn('group/spotlight-link relative flex w-full flex-1 @md:w-auto')" :to="activeUrl">
                    <Transition :name="hasMounted ? 'banner-fade' : ''">
                        <div :key="activeFolder?.id" class="flex size-full flex-col items-center justify-end gap-3 @md:flex-row @md:items-end @md:justify-start">
                            <LazyImage
                                alt="poster"
                                :class="cn('aspect-2-3 w-full rounded-md object-cover transition-[zoom] group-hover/spotlight-link:zoom-110')"
                                :src="activeFolder?.series?.poster_image?.path ?? handleStorageURL(activeFolder?.series?.thumbnail_url) ?? '/storage/thumbnails/default.webp'"
                                :wrapper-class="cn('shrink-0 shadow-sm relative', 'w-32 sm:w-24 2xl:w-30 3xl:w-36 opacity-100 ease-in h-fit')"
                            />

                            <div class="flex flex-col gap-0.5 text-center @md:text-start">
                                <span class="text-xs tracking-wide whitespace-nowrap uppercase group-hover/spotlight-link:text-white/75">{{ activeItem.label }}</span>

                                <h1 class="line-clamp-2 text-xl font-semibold text-balance capitalize md:text-2xl">
                                    {{ activeFolder?.title }}
                                </h1>
                                <p v-if="activeFolder?.series?.description" class="xs:line-clamp-1 hidden max-w-xl text-sm text-pretty sm:line-clamp-2">
                                    {{ activeFolder.series.description }}
                                </p>
                                <div
                                    class="@md:-mx-1: mt-1 flex h-5 w-full flex-wrap justify-center gap-1 overflow-clip [overflow-clip-margin:4px] @md:justify-start"
                                    v-if="activeFolder.series?.folder_tags?.length"
                                >
                                    <MediaTag
                                        v-for="tag in activeFolder.series.folder_tags.slice(0, Math.min(4, activeFolder.series.folder_tags.length))"
                                        :key="tag.id"
                                        class="bg-surface-3! text-foreground-0! py-0.5 text-xs dark:bg-neutral-900!"
                                    >
                                        {{ tag.name }}
                                    </MediaTag>
                                </div>
                                <div class="mt-1 hidden! h-8 @md:block"></div>
                            </div>
                        </div>
                    </Transition>
                </RouterLink>
                <div v-if="items.length > 1" class="mx-auto flex h-fit w-full max-w-2/3 min-w-40 items-center @md:mx-0 @md:max-w-52" role="tablist">
                    <ButtonBase
                        v-for="(item, index) in items"
                        role="tab"
                        type="button"
                        class="group/spotlight-nav block h-3 flex-1 px-0.5 py-1"
                        :key="item.folder.id"
                        :title="`Show spotilight item ${index + 1}/${items.length}`"
                        :aria-label="`Show spotilight item ${index + 1}/${items.length}`"
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
.banner-fade-enter-active,
.banner-fade-leave-active {
    position: absolute;
    inset: 0;
    transition: opacity 0.7s ease;
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
