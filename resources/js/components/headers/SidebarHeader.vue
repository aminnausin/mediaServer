<script setup lang="ts">
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { RouterLink } from 'vue-router';

const { selectedSideBar } = storeToRefs(useAppStore());

defineProps<{ text?: string; title?: string; to?: string }>();
</script>

<template>
    <div class="flex flex-col gap-2 lg:py-1">
        <component :is="to ? RouterLink : 'div'" :to="to" class="flex items-center justify-between">
            <h2 id="sidebar-title" class="h-8 w-full truncate text-2xl capitalize" :title="title">
                <slot name="title">{{ text ?? selectedSideBar }}</slot>
            </h2>
            <slot></slot>
        </component>

        <hr class="text-hr hidden lg:block" />
    </div>
</template>
