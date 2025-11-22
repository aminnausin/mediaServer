<script setup lang="ts">
import { onBeforeMount } from 'vue';
import { getCategories } from '@/service/mediaAPI';
import { useRouter } from 'vue-router';
import { toast } from '@/service/toaster/toastService';

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
    <div></div>
</template>
