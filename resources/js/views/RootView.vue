<script setup lang="ts">
import { SvgSpinners90RingWithBg } from '@/components/cedar-ui/icons';
import { onBeforeMount } from 'vue';
import { getCategories } from '@/service/mediaAPI';
import { useRouter } from 'vue-router';
import { toast } from '@aminnausin/cedar-ui';

const router = useRouter();

onBeforeMount(async () => {
    try {
        const { data } = await getCategories();

        if (data?.data?.length == 0) toast.warning('Warning', { description: 'No libraries exist yet.' });

        const defaultPath = data?.data?.[0]?.name ?? 'setup';
        router.replace(`/${defaultPath}`);
    } catch {
        router.replace('/setup');
    }
});
</script>
<template>
    <div class="flex h-screen flex-col items-center justify-center">
        <SvgSpinners90RingWithBg class="size-12" />
    </div>
</template>
