<script setup lang="ts">
import { useSettingsTabs } from '@/components/panels/SettingsTabs';
import { getScreenSize } from '@/service/util';
import { useAppStore } from '@/stores/AppStore';
import { onMounted } from 'vue';

import SettingsPreferences from '@/components/settings/SettingsPreferences.vue';
import SettingsAccount from '@/components/settings/SettingsAccount.vue';
import SettingsSidebar from '@/components/panels/SettingsSidebar.vue';
import LayoutBase from '@/layouts/LayoutBase.vue';

const { activeSettingsTab } = useSettingsTabs();
const { cycleSideBar } = useAppStore();

onMounted(async () => {
    const screenSize = getScreenSize();

    if (screenSize === 'default' || screenSize === 'sm' || screenSize === 'md') return;

    cycleSideBar('settings', 'left-card', false);
});
</script>

<template>
    <LayoutBase>
        <template v-slot:content>
            <section id="content-settings" class="3xl:min-h-[60vh] flex flex-col gap-4 text-sm lg:min-h-[80vh]">
                <SettingsPreferences v-if="activeSettingsTab?.name == 'preferences'" />
                <SettingsAccount v-if="activeSettingsTab?.name == 'account'" />
            </section>
        </template>
        <template v-slot:leftSidebar>
            <SettingsSidebar />
        </template>
    </LayoutBase>
</template>
