<script setup lang="ts">
import RegisterForm from '@/components/forms/RegisterForm.vue';
import AuthHeader from '@/components/headers/AuthHeader.vue';
import LayoutAuth from '@/layouts/LayoutAuth.vue';
import AuthCard from '@/components/cards/layout/AuthCard.vue';
</script>

<template>
    <LayoutAuth>
        <AuthHeader>Enter your details below to create your account</AuthHeader>
        <AuthCard>
            <RegisterForm />
        </AuthCard>
    </LayoutAuth>
</template>
