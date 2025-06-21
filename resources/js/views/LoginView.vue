<script setup lang="ts">
import AuthHeader from '@/components/headers/AuthHeader.vue';
import LayoutAuth from '@/layouts/LayoutAuth.vue';
import LoginForm from '@/components/forms/LoginForm.vue';
import AuthCard from '@/components/cards/AuthCard.vue';
</script>

<template>
    <LayoutAuth>
        <AuthHeader>Enter your email and password below to log in</AuthHeader>
        <AuthCard>
            <LoginForm />
        </AuthCard>
    </LayoutAuth>
</template>
