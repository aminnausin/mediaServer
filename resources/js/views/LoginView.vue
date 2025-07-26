<script setup lang="ts">
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';

import AuthHeader from '@/components/headers/AuthHeader.vue';
import LayoutAuth from '@/layouts/LayoutAuth.vue';
import LoginForm from '@/components/forms/LoginForm.vue';
import AuthCard from '@/components/cards/AuthCard.vue';

const { appManifest } = storeToRefs(useAppStore());
</script>

<template>
    <LayoutAuth>
        <AuthHeader class="whitespace-pre-wrap">{{ appManifest?.loginMessage ?? 'Enter your email and password below to log in' }}</AuthHeader>
        <AuthCard>
            <LoginForm />
        </AuthCard>
    </LayoutAuth>
</template>
