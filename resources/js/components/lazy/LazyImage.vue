<script setup lang="ts">
import type { HTMLAttributes, ImgHTMLAttributes } from 'vue';

import { SvgSpinners90RingWithBg } from '@/components/cedar-ui/icons';
import { ref, useAttrs, watch } from 'vue';
import { cn } from '@aminnausin/cedar-ui';

import ProIconsPhotoOff from '@/components/icons/ProIconsPhotoOff.vue';

defineOptions({ inheritAttrs: false });

const props = withDefaults(defineProps<{ src?: string; alt?: string; loading?: ImgHTMLAttributes['loading']; wrapperClass?: HTMLAttributes['class']; animate?: boolean }>(), {
    loading: 'lazy',
});
const attrs = useAttrs();

const isLoading = ref(false);
const isLoaded = ref(false);
const isError = ref(false);

watch(
    () => props.src,
    (src) => {
        isError.value = false;
        isLoading.value = !!src;
        isLoaded.value = false;
    },
    { immediate: true },
);
</script>
<template>
    <div :class="cn('block h-full w-full', { static: !isLoading && !isError }, wrapperClass)">
        <div v-show="src && isLoading && !isError" class="absolute inset-0 flex items-center justify-center">
            <SvgSpinners90RingWithBg class="size-4" />
        </div>
        <div v-show="isError && !isLoading" class="absolute inset-0 flex items-center justify-center gap-1 pb-6">
            <ProIconsPhotoOff class="size-5" />Image failed to load ({{ alt }})
        </div>
        <img
            v-bind="attrs"
            :loading="loading"
            :alt="isError ? '' : alt"
            :src="src"
            :class="[{ 'lazy-image transition-opacity duration-700 ease-in-out': animate }, { loaded: isLoaded }]"
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
}
</style>
