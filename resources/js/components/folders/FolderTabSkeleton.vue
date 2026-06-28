<script setup lang="ts">
import FolderTab from '@/components/folders/FolderTab.vue';
import TableSkeleton from '../skeleton/composites/TableSkeleton.vue';
</script>
<template>
    <FolderTab class="animate-pulse">
        <div class="flex flex-col gap-1">
            <div class="flex flex-wrap justify-between gap-x-2 gap-y-1">
                <div class="flex flex-1 flex-col gap-2">
                    <div class="bg-foreground-1/20 h-7 w-48 rounded"></div>
                    <div class="bg-foreground-1/10 h-4 w-32 rounded"></div>
                </div>
                <div class="flex flex-col items-end gap-2">
                    <div class="bg-foreground-1/20 h-7 w-16 rounded"></div>
                    <div class="bg-foreground-1/10 h-4 w-28 rounded"></div>
                </div>
            </div>

            <div class="mt-1 flex flex-wrap gap-1">
                <div class="bg-foreground-1/10 h-5 w-16 rounded-xl"></div>
                <div class="bg-foreground-1/10 h-5 w-14 rounded-xl"></div>
                <div class="bg-foreground-1/10 h-5 w-20 rounded-xl"></div>
            </div>
        </div>

        <div class="mt-1 flex flex-col gap-1.5">
            <div class="bg-foreground-1/10 h-3 w-full rounded"></div>
            <div class="bg-foreground-1/10 h-3 w-full rounded"></div>
            <div class="bg-foreground-1/10 h-3 w-full rounded"></div>
            <div class="bg-foreground-1/10 h-3 w-3/4 rounded"></div>
        </div>

        <div class="mt-2 grid grid-cols-2 gap-2 @sm:grid-cols-4">
            <div v-for="i in 4" :key="i" class="bg-surface-3 flex flex-col items-center gap-1.5 rounded-lg p-3">
                <div class="bg-foreground-1/20 h-5 w-12 rounded"></div>
                <div class="bg-foreground-1/10 h-3 w-16 rounded"></div>
            </div>
        </div>
    </FolderTab>
    <div class="flex flex-1 animate-pulse flex-col gap-3 @[40rem]:flex-row">
        <FolderTab class="w-full @[40rem]:max-w-40">
            <div v-for="i in 5" :key="i" class="mt-1 flex flex-wrap gap-1">
                <div class="bg-foreground-1/20 h-4 w-14 rounded-xl"></div>
                <div class="bg-foreground-1/10 h-3 w-20 rounded-xl"></div>
            </div>
        </FolderTab>
        <FolderTab class="flex-1 gap-2">
            <TableSkeleton class="*:*:min-h-0! *:animate-none" :table-length="4" />
        </FolderTab>
    </div>
</template>
