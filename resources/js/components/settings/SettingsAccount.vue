<script setup lang="ts">
import type { BreadCrumbItem } from '@/types/types';

import { BreadCrumbs } from '@/components/cedar-ui/breadcrumbs';
import { computed } from 'vue';

import SettingsDeleteAccount from '@/components/settings/account/SettingsDeleteAccount.vue';
import SettingsSessions from '@/components/settings/account/SettingsSessions.vue';
import ChangePassword from '@/components/forms/ChangePassword.vue';
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
    <section class="flex flex-wrap items-center justify-between gap-2">
        <BreadCrumbs :bread-crumbs="breadCrumbs" />
    </section>
    <ChangePassword />
    <ChangeEmail />
    <SettingsSessions />
    <SettingsDeleteAccount />
</template>
