<script setup lang="ts">
import ResetPassword from '@/components/forms/ResetPassword.vue';
import AuthHeader from '@/components/headers/AuthHeader.vue';
import LayoutAuth from '@/layouts/LayoutAuth.vue';
import AuthCard from '@/components/cards/AuthCard.vue';
</script>

<template>
    <LayoutAuth>
        <AuthHeader> Please enter your new password below </AuthHeader>
        <AuthCard>
            <ResetPassword />
        </AuthCard>
    </LayoutAuth>
</template>
