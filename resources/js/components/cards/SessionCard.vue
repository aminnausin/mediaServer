<script setup lang="ts">
import type { Session } from '@/types/model';

import { UAParser } from 'ua-parser-js';
import { computed } from 'vue';

import IconMobile from '@/components/icons/IconMobile.vue';

import CircumMonitor from '~icons/circum/monitor';

const props = defineProps<{ session: Session }>();
const agent = computed(() => {
    const parser = new UAParser(props.session.user_agent);
    return {
        parser,
        os: parser.getOS() || 'Unknown',
        browser: parser.getBrowser().name || 'Unknown',
        deviceType: parser.getDevice().type || 'desktop',
        isDesktop: parser.getDevice().type === undefined,
    };
});
</script>

<template>
    <section class="flex items-center gap-2">
        <CircumMonitor v-if="agent.isDesktop" class="size-8 text-neutral-600 dark:text-neutral-400" />
        <IconMobile v-else class="size-8 text-neutral-600 dark:text-neutral-400" />
        <section>
            <div class="text-sm">
                {{ agent.os }} -
                {{ agent.browser }}
            </div>

            <div class="text-xs text-neutral-600 dark:text-neutral-400">
                <span class="capitalize">{{ session.ip_address }},</span>

                <span v-if="session.is_current" class="font-semibold text-green-500 animate-pulse ms-1">
                    <span class="recent rounded-sm bg-green-500 size-2 inline-block animate-pulse"></span>
                    This device
                </span>
                <span v-else>Last active {{ session.last_active }}</span>
            </div>
        </section>
    </section>
</template>

<style lang="css" scoped>
.recent {
    box-shadow: 0 0 4px rgba(108, 198, 68, 0.5);
}
</style>
