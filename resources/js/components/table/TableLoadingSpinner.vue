<script setup lang="ts">
import SvgSpinners90RingWithBg from '~icons/svg-spinners/90-ring-with-bg';

const props = withDefaults(defineProps<{ isLoading?: boolean; noResultsMessage?: string; dataLength?: number }>(), { noResultsMessage: 'No Results' });
</script>
<template>
    <div class="col-span-full flex w-full items-center justify-center gap-2 text-center text-lg tracking-wider text-gray-500 uppercase dark:text-gray-400">
        <p>{{ isLoading ? '...Loading' : noResultsMessage }}</p>
        <SvgSpinners90RingWithBg v-show="isLoading" />
    </div>
</template>
