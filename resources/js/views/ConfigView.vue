<script setup lang="ts">
import { useConfigTabs } from '@/components/config/ConfigTabs';
import { getScreenSize } from '@/service/util';
import { useAppStore } from '@/stores/AppStore';
import { useRouter } from 'vue-router';
import { onMounted } from 'vue';
import { useAuth } from '@/composables/auth/useAuth';

import ConfigGeneral from '@/components/config/ConfigGeneral.vue';
import LeftSidebar from '@/components/panels/LeftSidebar.vue';
import LayoutBase from '@/layouts/LayoutBase.vue';

const { activeConfigTab } = useConfigTabs();
const { cycleSideBar } = useAppStore();

const { isAdmin } = useAuth();
const router = useRouter();

if (!isAdmin.value) {
    router.replace('/settings');
}

onMounted(async () => {
    const screenSize = getScreenSize();

    if (screenSize === 'default' || screenSize === 'sm' || screenSize === 'md') return;

    cycleSideBar('config', 'left-card', false);
});
</script>

<template>
    <LayoutBase>
        <template v-slot:content>
            <section id="content-config" class="page-height flex flex-col gap-4 text-sm">
                <ConfigGeneral v-if="activeConfigTab?.name == 'general'" />
            </section>
        </template>
        <template v-slot:leftSidebar>
            <LeftSidebar />
        </template>
    </LayoutBase>
</template>
