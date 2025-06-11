<script setup lang="ts">
import type { FormField } from '@/types/types';

import { useRouter, useRoute, RouterLink } from 'vue-router';
import { useAuthStore } from '@/stores/AuthStore';
import { storeToRefs } from 'pinia';
import { login } from '@/service/authAPI';
import { ref } from 'vue';

import FormInputLabel from '@/components/labels/FormInputLabel.vue';
import FormErrorList from '@/components/labels/FormErrorList.vue';
import AuthHeader from '@/components/headers/AuthHeader.vue';
import LayoutAuth from '@/layouts/LayoutAuth.vue';
import FormInput from '@/components/inputs/FormInput.vue';
import AuthCard from '@/components/cards/AuthCard.vue';
import useForm from '@/composables/useForm';
import BaseForm from '@/components/forms/BaseForm.vue';
import FormItem from '@/components/forms/FormItem.vue';
import ButtonForm from '@/components/inputs/ButtonForm.vue';

const { userData } = storeToRefs(useAuthStore());
const router = useRouter();
const route = useRoute();

const fields = ref<FormField[]>([
    { name: 'email', text: 'Email', type: 'text', required: true, autocomplete: 'username email' },
    { name: 'password', text: 'Password', type: 'password', required: true, autocomplete: 'password' },
]);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const handleLogin = async () => {
    form.submit(
        async (fields: any) => {
            return await login(fields);
        },
        {
            onSuccess: (response: { data: { data: { token: string; user: null } } }) => {
                localStorage.setItem('auth-token', response.data.data.token);
                userData.value = response.data.data.user;
                router.push(route.query.redirect ? route.query.redirect.toString() : '/');
            },
            onError: (errors: any) => form.reset('password'),
        },
    );
};
</script>

<template>
    <LayoutAuth>
        <AuthHeader>Enter your email and password below to log in</AuthHeader>
        <AuthCard>
            <!-- Session Status -->
            <BaseForm @submit.prevent="handleLogin">
                <FormItem v-for="(field, index) in fields" :key="index">
                    <span v-if="field.name === 'password'" class="flex flex-wrap">
                        <FormInputLabel :field="field" class="me-auto" />
                        <RouterLink
                            to="/recovery"
                            class="underline text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                        >
                            Forgot password?
                        </RouterLink>
                    </span>
                    <FormInputLabel v-else :field="field" />
                    <FormInput v-model="form.fields[field.name]" :field="field" class="!mt-0" />
                    <FormErrorList :errors="form.errors" :field-name="field.name" />
                </FormItem>

                <!-- Remember Me -->

                <div class="flex flex-wrap gap-2 gap-x-4 items-center justify-center sm:justify-end text-center">
                    <label for="remember-me" class="w-full flex items-center gap-2">
                        <input
                            v-model="form.fields.remember"
                            id="remember-me"
                            type="checkbox"
                            class=""
                            :class="[
                                'rounded dark:bg-neutral-900 border-neutral-300 dark:border-neutral-700 shadow-sm',
                                'appearance-none',
                                'focus:ring-indigo-500 focus:!ring-[0.125rem] !ring-offset-0',
                                'checked:text-indigo-600',
                            ]"
                            name="remember_me"
                        />
                        <span class="text-sm text-gray-600 dark:text-gray-400">Remember me</span>
                    </label>

                    <RouterLink
                        class="underline text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                        to="/register"
                    >
                        Not Registered?
                    </RouterLink>
                    <ButtonForm variant="auth" type="button" @click="handleLogin" :disabled="form.processing"> Log in </ButtonForm>
                </div>
            </BaseForm>
        </AuthCard>
    </LayoutAuth>
</template>
