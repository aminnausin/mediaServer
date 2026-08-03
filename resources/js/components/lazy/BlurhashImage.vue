<script setup lang="ts">
import type { HTMLAttributes, ImgHTMLAttributes } from 'vue';

import { computed, nextTick, onBeforeUnmount, onMounted, ref, useAttrs, watch } from 'vue';
import { SvgSpinners90RingWithBg } from '@/components/cedar-ui/icons';
import { decode } from 'blurhash';
import { cn } from '@aminnausin/cedar-ui';

import ProIconsPhotoOff from '@/components/icons/ProIconsPhotoOff.vue';

defineOptions({ inheritAttrs: false });

const props = withDefaults(
    defineProps<{
        src?: string;
        alt?: string;
        fetchPriority?: ImgHTMLAttributes['fetchpriority'];
        wrapperClass?: HTMLAttributes['class'];
        animate?: boolean;
        blurhash?: string;
        rootMargin?: string;
    }>(),
    {
        rootMargin: '144px',
        animate: true,
    },
);

const attrs = useAttrs();

const containerRef = ref<HTMLDivElement>();
const canvasRef = ref<HTMLCanvasElement>();

const isLoading = ref(false);
const isLoaded = ref(false);
const isError = ref(false);
const isVisible = ref(false);

let observer: IntersectionObserver | null = null;

const activeSrc = computed(() => (isVisible.value ? props.src : undefined));

const blurhash = ref(props.blurhash);

const drawBlurhash = () => {
    if (!blurhash.value || !canvasRef.value) return;

    try {
        const w = 32;
        const h = 32;
        const pixels = decode(blurhash.value, w, h);
        const ctx = canvasRef.value.getContext('2d');

        if (!ctx) throw new Error('Blurhash canvas failed to load');

        canvasRef.value.width = w;
        canvasRef.value.height = h;

        const imageData = ctx.createImageData(w, h);
        imageData.data.set(pixels);
        ctx.putImageData(imageData, 0, 0);
    } catch {
        // fall back to spinner
        blurhash.value = undefined;
    }
};

const startLoadingIfVisible = () => {
    if (isVisible.value) isLoading.value = !!props.src;
};

const setupObserver = () => {
    observer?.disconnect();
    observer = null;

    if (!containerRef.value || typeof IntersectionObserver === 'undefined') {
        isVisible.value = true;
        startLoadingIfVisible();
        return;
    }

    // starts loading image when within {rootMargin} of the viewport and kills observer
    observer = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    isVisible.value = true;
                    startLoadingIfVisible();
                    observer?.disconnect();
                    observer = null;
                    break;
                }
            }
        },
        { rootMargin: props.rootMargin },
    );

    observer.observe(containerRef.value);
};

watch(
    () => props.src,
    () => {
        isError.value = false;
        isLoaded.value = false;
        startLoadingIfVisible();
    },
);

watch(
    () => props.blurhash,
    (val) => {
        blurhash.value = val;
        if (val) drawBlurhash();
    },
);

onMounted(async () => {
    if (blurhash.value) drawBlurhash();

    await nextTick();
    setupObserver();
});

onBeforeUnmount(() => {
    observer?.disconnect();
    observer = null;
});
</script>
<template>
    <div ref="containerRef" :class="cn('relative block h-full w-full', wrapperClass)">
        <canvas v-if="blurhash" ref="canvasRef" v-show="!isError" :class="cn('absolute inset-0 z-0 h-full w-full object-cover', $attrs.class)" aria-hidden="true" />
        <div v-else v-show="src && isLoading && !isError" class="absolute inset-0 flex items-center justify-center">
            <SvgSpinners90RingWithBg class="size-4" />
        </div>
        <div v-show="isError && !isLoading" class="absolute inset-0 flex flex-wrap items-center justify-center gap-1 p-2 pb-6 text-center text-xs">
            <ProIconsPhotoOff class="size-5" /><span> Image failed to load ({{ alt }}) </span>
        </div>
        <img
            v-bind="attrs"
            decoding="async"
            :fetchpriority="fetchPriority"
            :alt="isError || !isLoaded ? '' : alt"
            :src="activeSrc"
            :class="['lazy-image relative', { 'lazy-image transition-opacity duration-300 ease-in-out': animate }, { loaded: isLoaded }]"
            @load="
                isLoading = false;
                isError = false;
                isLoaded = true;
            "
            @error="
                isError = true;
                isLoading = false;
            "
        />
    </div>
</template>
<style lang="css" scoped>
.lazy-image {
    opacity: 0;
}

.lazy-image.loaded {
    opacity: 1;
    z-index: 1;
}
</style>
