<script setup lang="ts">
import FolderOverviewSidebar from '@/components/folders/FolderOverviewSidebar.vue';
import FolderDescription from '@/components/folders/FolderDescription.vue';
import FolderMedia from '@/components/folders/FolderMedia.vue';
</script>

<template>
    <FolderDescription />
    <div class="flex flex-1 flex-col gap-3 @[40rem]:flex-row">
        <FolderOverviewSidebar />
        <FolderMedia :useBackground="true" />
    </div>
</template>
