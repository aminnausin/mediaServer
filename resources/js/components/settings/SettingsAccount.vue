<script setup lang="ts">
import type { BreadCrumbItem } from '@/types/types';

import { computed } from 'vue';

import SettingsDeleteAccount from '@/components/settings/account/SettingsDeleteAccount.vue';
import SettingsSessions from '@/components/settings/account/SettingsSessions.vue';
import ChangePassword from '@/components/forms/ChangePassword.vue';
import BreadCrumbs from '@/components/pinesUI/BreadCrumbs.vue';
import ChangeEmail from '@/components/forms/ChangeEmail.vue';

import ProiconsSettings from '~icons/proicons/settings';
import LucideUser from '~icons/lucide/user';

const breadCrumbs = computed(() => {
    const items: BreadCrumbItem[] = [
        {
            name: 'Settings',
            url: '/settings/preferences',
            icon: ProiconsSettings,
        },
        {
            name: 'Account',
            url: '/Settings/Account',
            icon: LucideUser,
        },
    ];

    return items;
});
</script>

<template>
    <section class="flex items-center gap-2 justify-between flex-wrap">
        <BreadCrumbs :bread-crumbs="breadCrumbs" />
    </section>
    <ChangePassword />
    <ChangeEmail />
    <SettingsSessions />
    <SettingsDeleteAccount />
</template>
