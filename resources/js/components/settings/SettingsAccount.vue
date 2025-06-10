<script setup lang="ts">
import SettingsDeleteAccount from '@/components/settings/account/SettingsDeleteAccount.vue';
import SettingsSessions from '@/components/settings/account/SettingsSessions.vue';
import ChangePassword from '@/components/forms/ChangePassword.vue';
import ChangeEmail from '@/components/forms/ChangeEmail.vue';
</script>

<template>
    <ChangePassword />
    <ChangeEmail />
    <SettingsSessions />
    <SettingsDeleteAccount />
</template>
