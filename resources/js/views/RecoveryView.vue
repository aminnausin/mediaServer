<script setup lang="ts">
import RecoveryForm from '@/components/forms/RecoveryForm.vue';
import AuthHeader from '@/components/headers/AuthHeader.vue';
import LayoutAuth from '@/layouts/LayoutAuth.vue';
import AuthCard from '@/components/cards/layout/AuthCard.vue';
</script>

<template>
    <LayoutAuth>
        <AuthHeader> Enter your email to receive a password reset link </AuthHeader>
        <AuthCard>
            <RecoveryForm />
        </AuthCard>
    </LayoutAuth>
</template>
