<script setup lang="ts">
import type { ImgHTMLAttributes } from 'vue';

import { SvgSpinners90RingWithBg } from '@/components/cedar-ui/icons';
import { ref, useAttrs, watch } from 'vue';

defineOptions({ inheritAttrs: false });

const props = withDefaults(defineProps<{ src?: string; alt?: string; loading?: ImgHTMLAttributes['loading'] }>(), { loading: 'lazy' });
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
    <div class="relative contents">
        <div v-if="src && isLoading && !isError" class="absolute inset-0 flex items-center justify-center">
            <SvgSpinners90RingWithBg class="size-4" />
        </div>
        <img
            v-bind="attrs"
            :key="src"
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
    transition-property: opacity, transform;
}
</style>
