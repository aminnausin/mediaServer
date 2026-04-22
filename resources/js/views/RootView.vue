<script setup lang="ts">
import { SvgSpinners90RingWithBg } from '@/components/cedar-ui/icons';
import { getSetupStatus } from '@/service/mediaAPI';
import { onBeforeMount } from 'vue';
import { useRouter } from 'vue-router';
import { toast } from '@aminnausin/cedar-ui';

import AuthHeader from '@/components/headers/AuthHeader.vue';

const router = useRouter();

onBeforeMount(async () => {
    try {
        const { data } = await getSetupStatus();
        if (!data.has_data) {
            toast.warning('Warning', {
                description: 'No libraries exist yet.',
            });
            router.replace('/setup');
            return;
        }

        if (!data.default_library?.name) {
            toast.error('Warning', {
                description: 'No libraries are available for your account.',
            });
            router.replace('/setup');
            return;
        }
        router.replace(`/${data.default_library.name}`);
    } catch (error: unknown) {
        console.error('Setup check failed:', error);
        toast.error('Error', {
            description: 'Setup check failed.',
        });
        router.replace('/setup');
    }
});
</script>
<template>
    <div class="flex h-screen flex-col items-center justify-center gap-8">
        <AuthHeader class="animate-pulse" />
        <SvgSpinners90RingWithBg class="size-8" />
        <div class="dark:bg-surface-2 hidden h-2 w-full max-w-1/2 overflow-clip rounded-full bg-neutral-200 sm:max-w-64">
            <div
                class="h-full rounded-full bg-neutral-300"
                :style="{
                    width: '34%',
                }"
            ></div>
        </div>
    </div>
</template>
