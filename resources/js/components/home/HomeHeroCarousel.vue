<script setup lang="ts">
import type { FolderResource } from '@/contracts/media';

import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { handleStorageURL } from '@/service/util';
import { ButtonBase } from '@/components/cedar-ui/button';
import { cn } from '@aminnausin/cedar-ui';

import LazyImage from '@/components/lazy/LazyImage.vue';

import CircumPlay1 from '~icons/circum/play-1';

let timer: ReturnType<typeof setTimeout> | null = null;

const props = withDefaults(defineProps<{ items: FolderResource[]; intervalMs?: number }>(), { intervalMs: 6000 });

const activeIndex = ref(0);
const isPaused = ref(false);

const activeFolder = computed(() => props.items[activeIndex.value] ?? null);

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

const restart = () => {
    if (timer) clearTimeout(timer);
    if (isPaused.value || props.items.length <= 1) return;
    timer = setTimeout(nextFolder, props.intervalMs);
};

const goTo = (index: number) => {
    activeIndex.value = index;
};

watch(activeIndex, restart);
watch(isPaused, restart);

onMounted(restart);
onBeforeUnmount(() => timer && clearTimeout(timer));
</script>

<template>
    <RouterLink
        :to="activeUrl"
        :class="
            cn(
                'ring-r-default/5 group relative block h-[clamp(200px,28vw,380px)] w-full overflow-hidden rounded-xl text-white ring-1',
                'content-auto [contain-intrinsic-size:auto_300px]',
            )
        "
        @mouseenter="isPaused = true"
        @mouseleave="isPaused = false"
    >
        <div class="absolute inset-0">
            <TransitionGroup name="banner-fade">
                <LazyImage
                    v-for="folder in [activeFolder]"
                    :key="folder?.id"
                    :src="bannerSrc"
                    :alt="folder?.title"
                    class="absolute inset-0 size-full object-cover"
                    loading="eager"
                    decoding="async"
                />
            </TransitionGroup>
        </div>

        <div class="absolute inset-0 flex w-full items-end gap-4 bg-linear-to-b from-transparent to-neutral-950/40 p-3 text-white">
            <div :class="cn('flex flex-1 items-end gap-4 transition-[gap] duration-300 ease-in', { 'gap-0 ease-out': !activeFolder.series?.banner_image?.path })">
                <LazyImage
                    alt="poster"
                    :class="cn('aspect-2-3 w-full max-w-24 rounded-md object-cover')"
                    :src="activeFolder?.series?.poster_image?.path ?? handleStorageURL(activeFolder?.series?.thumbnail_url) ?? '/storage/thumbnails/default.webp'"
                    :wrapper-class="
                        cn(
                            'relative overflow-hidden transition-[width,height,margin, opacity] duration-300 ease-out origin-bottom-left shrink-0',
                            activeFolder?.series?.banner_image?.path ? 'w-24 h-36 opacity-100 ease-in' : 'w-0 h-0 opacity-0',
                        )
                    "
                />

                <div class="flex flex-col gap-2">
                    <span class="text-xs tracking-wide uppercase">New series</span>
                    <h1 class="text-xl font-semibold md:text-2xl">{{ activeFolder?.title }}</h1>
                    <p v-if="activeFolder?.series?.description" class="hidden max-w-xl text-sm sm:line-clamp-2">
                        {{ activeFolder.series.description }}
                    </p>
                    <!-- <ButtonBase class="text-foreground-0 dark:text-foreground-i mt-1 w-fit gap-2 bg-white"> <CircumPlay1 class="size-4" /> View </ButtonBase> -->
                </div>
            </div>
            <div v-if="items.length > 1" class="pointer-events-none ms-auto flex max-w-64 flex-1 items-center">
                <ButtonBase
                    v-for="(item, index) in items"
                    :key="item.id"
                    type="button"
                    class="pointer-events-auto block h-3 flex-1 px-0.5 py-1"
                    :aria-label="`Show ${item.title}`"
                    @click.prevent="goTo(index)"
                >
                    <div class="h-1 overflow-hidden rounded-full bg-white/25">
                        <div
                            :class="
                                cn('bg-primary h-full', {
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
    </RouterLink>
</template>
<style lang="css" scoped>
.banner-fade-enter-active {
    transition: opacity 0.4s ease;
}
.banner-fade-enter-from {
    opacity: 0;
}
.banner-fade-leave-active {
    position: absolute;
    inset: 0;
}

.hero-fill {
    animation-name: fill;
    animation-timing-function: linear;
    animation-fill-mode: forwards;
}

@keyframes fill {
    from {
        width: 0%;
    }
    to {
        width: 100%;
    }
}
</style>
