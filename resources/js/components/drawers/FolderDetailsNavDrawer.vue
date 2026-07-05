<script setup lang="ts">
import { useContentStore } from '@/stores/ContentStore';
import { useFolderTabs } from '@/components/folders/FolderTabs';
import { storeToRefs } from 'pinia';
import { BaseDrawer } from '@/components/cedar-ui/drawer';
import { useDrawer } from '@aminnausin/cedar-ui';
import { useRoute } from 'vue-router';
import { watch } from 'vue';

import DashboardSidebarCard from '@/components/cards/sidebar/DashboardSidebarCard.vue';
import SidebarHeader from '@/components/headers/SidebarHeader.vue';

const drawer = useDrawer();
const route = useRoute();

const { stateFolder, isStateFolderAudio: isAudio } = storeToRefs(useContentStore());
const { activeFolderTab, tabs } = useFolderTabs(stateFolder, isAudio);

const { baseUrl } = drawer.props.value.payload as { baseUrl: string };

watch(
    () => route.fullPath,
    () => drawer.close('programmatic'),
);
</script>

<template>
    <BaseDrawer>
        <SidebarHeader :text="'Folder Details'" />
        <div class="full-height-sidebar flex h-full flex-1 flex-col gap-2">
            <DashboardSidebarCard
                v-for="(tab, index) in tabs"
                :key="index"
                :to="`${baseUrl}/details/${tab.name}`"
                :disabled="tab.disabled"
                :is-active="activeFolderTab?.name === tab.name"
            >
                <template #header>
                    <h3 class="w-full flex-1 truncate" :title="tab.title ?? tab.name">{{ tab.title ?? tab.name }}</h3>
                    <component v-if="tab.icon" :is="tab.icon" class="ml-auto size-5" />
                </template>
                <template #body>
                    <h4 v-if="tab.description" title="Description" class="w-full flex-1 truncate text-wrap sm:text-nowrap">
                        {{ tab.description }}
                    </h4>
                    <h4 v-if="tab.info" title="Information" class="w-fit truncate text-nowrap sm:text-right">
                        {{ tab.info.value }}
                    </h4>
                </template>
            </DashboardSidebarCard>
        </div>
    </BaseDrawer>
</template>
