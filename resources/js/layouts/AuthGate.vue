<script setup lang="ts">
//UNUSED
import { SvgSpinners90RingWithBg } from '@/components/cedar-ui/icons';
import { useAuthStore } from '@/stores/AuthStore';
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';

const route = useRoute();

const { userData, isLoadingUserData } = storeToRefs(useAuthStore());
</script>

<template>
    <slot v-if="!route.meta?.protected || (!isLoadingUserData && userData)"></slot>
    <div v-else class="col-span-full flex w-full items-center justify-center gap-2 text-center text-lg tracking-wider text-gray-500 uppercase dark:text-gray-400">
        <p>{{ '...Loading' }}</p>
        <SvgSpinners90RingWithBg />
    </div>
</template>
