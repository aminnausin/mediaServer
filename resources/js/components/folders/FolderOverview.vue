<script setup lang="ts">
import FolderOverviewSidebar from '@/components/folders/FolderOverviewSidebar.vue';
import FolderDescription from '@/components/folders/FolderDescription.vue';
import FolderRelations from '@/components/folders/FolderRelations.vue';
import FolderMedia from '@/components/folders/FolderMedia.vue';
</script>

<template>
    <div class="flex flex-col gap-2">
        <FolderDescription />
        <div class="flex flex-1 flex-col gap-2 @[40rem]:flex-row">
            <FolderOverviewSidebar />
            <div class="flex flex-1 flex-col gap-2">
                <FolderRelations :hide-if-empty="true" :show-title="true" />
                <FolderMedia />
            </div>
        </div>
    </div>
</template>
