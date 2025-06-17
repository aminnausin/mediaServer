<script setup lang="ts">
//UNUSED
import { useAuthStore } from '@/stores/AuthStore';
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';

import SvgSpinners90RingWithBg from '~icons/svg-spinners/90-ring-with-bg';

const route = useRoute();

const { userData, isLoadingUserData } = storeToRefs(useAuthStore());
</script>

<template>
    <slot v-if="!route.meta?.protected || (!isLoadingUserData && userData)"></slot>
    <div v-else class="col-span-full flex items-center justify-center text-center text-lg text-gray-500 dark:text-gray-400 uppercase tracking-wider w-full gap-2">
        <p>{{ '...Loading' }}</p>
        <SvgSpinners90RingWithBg />
    </div>
</template>
