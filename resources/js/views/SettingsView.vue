<script setup lang="ts">
import type { SidebarTabItem } from '@/types/types';

import { computed, onMounted, ref, watch } from 'vue';
import { useAuthStore } from '@/stores/AuthStore';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';

import SettingsPreferences from '@/components/settings/SettingsPreferences.vue';
import SettingsAccount from '@/components/settings/SettingsAccount.vue';
import AppManifestCard from '@/components/cards/AppManifestCard.vue';
import SidebarCard from '@/components/cards/SidebarCard.vue';
import LayoutBase from '@/layouts/LayoutBase.vue';

import ProiconsSettings from '~icons/proicons/settings';
import CircumGrid31 from '~icons/circum/grid-3-1';
import LucideUser from '~icons/lucide/user';

const { pageTitle, selectedSideBar } = storeToRefs(useAppStore());
const { cycleSideBar } = useAppStore();
const { userData } = storeToRefs(useAuthStore());

const dev = true;

const settingsTab = ref<{ name: string; title?: string; icon?: any }>();

const settingsTabs = computed<SidebarTabItem[]>(() => {
    return [
        {
            name: 'preferences',
            title: 'Preferences',
            description: 'Website Preferences', // Remember Volume, Ambient Mode, Playback Heatmap, Opt out of things?
            icon: ProiconsSettings,
        },
        {
            name: 'account',
            title: 'Account',
            description: 'Account Settings', // Email Password Sessions
            disabled: !userData.value,
            icon: LucideUser,
        },
        {
            name: 'profile',
            title: 'Profile',
            description: 'Profile Settings', // Visibility, Avatar, Banner
            disabled: dev || !userData.value,
        },
        {
            name: 'server',
            title: 'Server',
            description: 'Server Settings', // Disable features
            disabled: dev || !userData.value || userData.value?.id !== 1,
        },
    ];
});

const route = useRoute();

onMounted(async () => {
    cycleSideBar('settings', 'left-card', false);
});

watch(
    () => route?.params?.tab,
    (URL_TAB) => {
        if (!URL_TAB) return;
        const defaultTab = settingsTabs.value.find((tab) => tab.title === URL_TAB || tab.name === URL_TAB) ?? settingsTabs.value[0];

        pageTitle.value = defaultTab.title ?? defaultTab.name;
        settingsTab.value = defaultTab;
    },
    { immediate: true },
);

watch(
    () => settingsTab.value,
    () => {
        if (!settingsTab.value) return;
        pageTitle.value = settingsTab.value.title ?? settingsTab.value.name;
    },
);
</script>

<template>
    <LayoutBase>
        <template v-slot:content>
            <section id="content-settings" class="3xl:min-h-[60vh] flex flex-col gap-4 text-sm lg:min-h-[80vh]">
                <SettingsPreferences v-if="settingsTab?.name == 'preferences'" />
                <SettingsAccount v-if="settingsTab?.name == 'account'" />
            </section>
        </template>
        <template v-slot:leftSidebar>
            <div class="flex flex-col gap-2 py-1">
                <h2 id="sidebar-title" class="h-8 w-full text-2xl capitalize dark:text-white">{{ selectedSideBar }}</h2>
                <hr class="" />
            </div>
            <section class="flex flex-1 flex-col gap-2">
                <SidebarCard
                    v-for="(tab, index) in settingsTabs.filter((tab) => !tab.disabled)"
                    :key="index"
                    :link="tab.disabled ? '' : `/settings/${tab.name}`"
                    :class="[
                        'items-center justify-between gap-2!',
                        'hover:bg-primary-800 overflow-hidden bg-white capitalize',
                        `ring-purple-600 ring-inset hover:ring-2 hover:ring-purple-600/50 ${settingsTab?.name == tab.name && 'ring-2'}`,
                        'aria-disabled:cursor-not-allowed aria-disabled:opacity-60 aria-disabled:hover:ring-neutral-200 dark:aria-disabled:hover:ring-neutral-700',
                    ]"
                    @click="settingsTab = tab"
                    :aria-disabled="tab.disabled"
                >
                    <template #header>
                        <h3 class="line-clamp-1 w-full flex-1 text-gray-900 dark:text-white" :title="tab.title ?? tab.name">{{ tab.title ?? tab.name }}</h3>
                        <component v-if="tab.icon" :is="tab.icon" class="ml-auto h-6 w-6" />
                    </template>
                    <template #body>
                        <h4 v-if="tab.description" title="Description" class="w-full flex-1 truncate text-wrap sm:text-nowrap">
                            {{ tab.description }}
                        </h4>
                        <h4 v-if="tab.info" title="Information" class="w-fit truncate text-nowrap sm:text-right">
                            {{ tab.info.value }}
                        </h4>
                    </template>
                </SidebarCard>
                <SidebarCard
                    :link="`/dashboard`"
                    :class="[
                        'items-center justify-between',
                        'hover:bg-primary-800 overflow-hidden bg-white capitalize',
                        'ring-purple-600 ring-inset hover:ring-2 hover:ring-purple-600/50',
                        'aria-disabled:cursor-not-allowed aria-disabled:opacity-60 aria-disabled:hover:ring-neutral-200 dark:aria-disabled:hover:ring-neutral-700',
                    ]"
                    :aria-disabled="false"
                >
                    <template #header>
                        <h3 class="text-gray-900 dark:text-white" :title="'Dashboard'">Dashboard</h3>
                        <CircumGrid31 class="ml-auto h-6 w-6" />
                    </template>
                    <template #body>
                        <h4 title="App Dashboard" class="w-full flex-1 truncate text-wrap sm:text-nowrap">Server Analytics</h4>
                    </template>
                </SidebarCard>
                <AppManifestCard />
            </section>
        </template>
    </LayoutBase>
</template>
