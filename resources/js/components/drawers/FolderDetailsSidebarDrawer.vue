<script setup lang="ts">
import { BaseDrawer } from '@/components/cedar-ui/drawer';
import { useDrawer } from '@aminnausin/cedar-ui';
import { useRoute } from 'vue-router';
import { watch } from 'vue';

import FolderDetailsSidebar from '@/components/panels/FolderDetailsSidebar.vue';

const drawer = useDrawer();
const route = useRoute();

watch(
    () => route.fullPath,
    () => drawer.close('programmatic'),
);
</script>

<template>
    <BaseDrawer>
        <FolderDetailsSidebar />
    </BaseDrawer>
</template>
