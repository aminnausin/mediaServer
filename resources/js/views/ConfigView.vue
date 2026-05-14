<script setup lang="ts">
import { useRoute, useRouter } from 'vue-router';
import { onMounted, watch } from 'vue';
import { useConfigTabs } from '@/components/config/ConfigTabs';
import { getScreenSize } from '@/service/util';
import { useAppStore } from '@/stores/AppStore';
import { useAuth } from '@/composables/auth/useAuth';

import ConfigPerformance from '@/components/config/ConfigPerformance.vue';
import ConfigGeneral from '@/components/config/ConfigGeneral.vue';
import LeftSidebar from '@/components/panels/LeftSidebar.vue';
import LayoutBase from '@/layouts/LayoutBase.vue';

const { activeConfigTab, setTab } = useConfigTabs();
const { cycleSideBar } = useAppStore();
const { isAdmin } = useAuth();

const router = useRouter();
const route = useRoute();

if (!isAdmin.value) {
    router.replace('/settings');
}

onMounted(async () => {
    const screenSize = getScreenSize();

    if (screenSize === 'default' || screenSize === 'sm' || screenSize === 'md') return;

    cycleSideBar('config', 'left-card', false);
});

watch(() => route.params.tab, setTab, { immediate: true });
</script>

<template>
    <LayoutBase>
        <template v-slot:content>
            <section id="content-config" class="page-height flex flex-col gap-4 text-sm">
                <ConfigGeneral v-if="activeConfigTab?.name == 'general'" />
                <ConfigPerformance v-if="activeConfigTab?.name == 'performance'" />
            </section>
        </template>
        <template v-slot:leftSidebar>
            <LeftSidebar />
        </template>
    </LayoutBase>
</template>
