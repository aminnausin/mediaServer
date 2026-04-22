<script setup lang="ts">
import type { Session } from '@/types/model';

import { onMounted, ref } from 'vue';

import IconMobile from '@/components/icons/IconMobile.vue';

import CircumMonitor from '~icons/circum/monitor';

const props = defineProps<{ session: Session }>();

const agent = ref({
    os: 'Loading...',
    browser: 'Loading...',
    isDesktop: true,
});

onMounted(async () => {
    const { UAParser } = await import('ua-parser-js');

    const parser = new UAParser(props.session.user_agent);
    const os = parser.getOS().name || 'Unknown';
    const browser = parser.getBrowser().name || 'Unknown';
    const deviceType = parser.getDevice().type;

    agent.value = {
        os,
        browser,
        isDesktop: deviceType === undefined,
    };
});
</script>

<template>
    <div class="text-foreground-1 flex items-center gap-2 text-xs">
        <CircumMonitor v-if="agent.isDesktop" class="size-8" />
        <IconMobile v-else class="size-8" />
        <section>
            <h4 class="text-foreground-0 text-sm">
                {{ agent.os }} -
                {{ agent.browser }}
            </h4>

            <div>
                <span class="capitalize">{{ session.ip_address }},</span>

                <span v-if="session.is_current" class="text-success ms-1 animate-pulse font-semibold">
                    <span class="recent bg-success inline-block size-2 animate-pulse rounded-sm"></span>
                    This device
                </span>
                <span v-else class="ms-1">Last active {{ session.last_active }}</span>
            </div>
        </section>
    </div>
</template>

<style lang="css" scoped>
.recent {
    box-shadow: 0 0 4px rgba(108, 198, 68, 0.5);
}
</style>
