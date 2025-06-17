<script setup lang="ts">
import { onBeforeMount } from 'vue';
import { getCategories } from '@/service/mediaAPI';
import { useRouter } from 'vue-router';

const router = useRouter();

onBeforeMount(async () => {
    try {
        const { data } = await getCategories();
        const defaultPath = data?.data?.[0]?.name ?? '/setup';
        router.replace(`/${defaultPath}`);
    } catch {
        router.replace('/setup');
    }
});
</script>
<template>
    <div></div>
</template>
