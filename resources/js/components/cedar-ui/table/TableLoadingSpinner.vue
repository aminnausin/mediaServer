<script setup lang="ts">
import { SvgSpinners90RingWithBg } from '../icons';

const props = withDefaults(defineProps<{ isLoading?: boolean; noResultsMessage?: string; dataLength?: number }>(), {
    noResultsMessage: 'No Results',
});
</script>
<template>
    <div class="text-foreground-2 col-span-full flex w-full items-center justify-center gap-2 text-center text-lg tracking-wider uppercase">
        <p>{{ isLoading ? '...Loading' : noResultsMessage }}</p>
        <SvgSpinners90RingWithBg v-show="isLoading" />
    </div>
</template>
