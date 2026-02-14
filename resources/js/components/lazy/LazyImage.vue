<script setup lang="ts">
import type { HTMLAttributes, ImgHTMLAttributes } from 'vue';

import { SvgSpinners90RingWithBg } from '@/components/cedar-ui/icons';
import { ref, useAttrs, watch } from 'vue';
import { cn } from '@aminnausin/cedar-ui';

defineOptions({ inheritAttrs: false });

const props = withDefaults(defineProps<{ src?: string; alt?: string; loading?: ImgHTMLAttributes['loading']; wrapperClass?: HTMLAttributes['class'] }>(), { loading: 'lazy' });
const attrs = useAttrs();

const isLoading = ref(false);
const isError = ref(false);
watch(
    () => props.src,
    (src) => {
        isError.value = false;
        isLoading.value = !!src;
    },
    { immediate: true },
);
</script>
<template>
    <div :class="cn('block h-full w-full', wrapperClass)">
        <div v-show="src && isLoading && !isError" class="absolute inset-0 flex items-center justify-center">
            <SvgSpinners90RingWithBg class="size-4" />
        </div>
        <img
            v-bind="attrs"
            :loading="loading"
            :alt="alt"
            :src="src"
            :class="[{ 'scale-85 opacity-0': isLoading }, 'lazy-image ease-in-out']"
            @load="
                isLoading = false;
                isError = false;
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
    will-change: transform, opacity;
    transition-property: opacity, transform;
}
</style>
