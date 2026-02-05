<script setup lang="ts">
import { ref, useAttrs } from 'vue';
import { SvgSpinners90RingWithBg } from '../cedar-ui/icons';

defineOptions({ inheritAttrs: false });

const props = defineProps<{ src?: string }>();
const attrs = useAttrs();

const isLoading = ref(true);
const isError = ref(false);
</script>
<template>
    <div class="relative contents">
        <div v-if="src && isLoading && !isError" class="absolute inset-0 flex items-center justify-center">
            <SvgSpinners90RingWithBg class="size-4" />
        </div>
        <img
            v-bind="attrs"
            :src="src"
            loading="lazy"
            :class="[{ 'scale-85 opacity-0': isLoading }, 'transition-all ease-in-out']"
            @load="
                isLoading = false;
                isError = false;
            "
            @error="isError = true"
        />
    </div>
</template>
