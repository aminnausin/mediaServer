<script setup lang="ts">
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';

const { selectedSideBar } = storeToRefs(useAppStore());
</script>

<template>
    <div class="flex flex-col gap-2 lg:py-1">
        <div class="flex items-center justify-between">
            <h2 id="sidebar-title" class="h-8 w-full truncate text-2xl capitalize">
                <slot name="title">{{ selectedSideBar }}</slot>
            </h2>
            <slot></slot>
        </div>

        <hr class="hidden lg:block" />
    </div>
</template>
