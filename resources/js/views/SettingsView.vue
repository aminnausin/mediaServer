<script setup lang="ts">
import { useSettingsTabs } from '@/components/settings/SettingsTabs';
import { getScreenSize } from '@/service/util';
import { useAppStore } from '@/stores/AppStore';
import { onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { watch } from 'vue';

import SettingsPreferences from '@/components/settings/SettingsPreferences.vue';
import SettingsAccount from '@/components/settings/SettingsAccount.vue';
import LeftSidebar from '@/components/panels/LeftSidebar.vue';
import LayoutBase from '@/layouts/LayoutBase.vue';

const { activeSettingsTab, setTab } = useSettingsTabs();
const { cycleSideBar } = useAppStore();

const route = useRoute();

onMounted(async () => {
    const screenSize = getScreenSize();

    if (screenSize === 'default' || screenSize === 'sm' || screenSize === 'md') return;

    cycleSideBar('settings', 'left-card', false);
});

watch(() => route.params.tab, setTab, { immediate: true });
</script>

<template>
    <LayoutBase>
        <template v-slot:content>
            <section id="content-settings" class="page-height flex flex-col gap-4 text-sm">
                <SettingsPreferences v-if="activeSettingsTab?.name == 'preferences'" />
                <SettingsAccount v-if="activeSettingsTab?.name == 'account'" />
            </section>
        </template>
        <template v-slot:leftSidebar>
            <LeftSidebar />
        </template>
    </LayoutBase>
</template>
