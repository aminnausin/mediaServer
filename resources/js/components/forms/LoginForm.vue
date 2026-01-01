<script setup lang="ts">
import type { UserResource } from '@/types/resources';
import type { FormField } from '@/types/types';

import { FormInput, FormLabel, FormErrorList } from '@/components/cedar-ui/form';
import { useRouter, useRoute, RouterLink } from 'vue-router';
import { useAuthStore } from '@/stores/AuthStore';
import { storeToRefs } from 'pinia';
import { ButtonForm } from '@/components/cedar-ui/button';
import { login } from '@/service/authAPI';
import { ref } from 'vue';

import BaseForm from '@/components/forms/BaseForm.vue';
import FormItem from '@/components/forms/FormItem.vue';
import useForm from '@/composables/useForm';

const { userData } = storeToRefs(useAuthStore());
const router = useRouter();
const route = useRoute();

const fields = ref<FormField[]>([
    { name: 'email', text: 'Email', type: 'text', required: true, autocomplete: 'username email', placeholder: 'Email' },
    { name: 'password', text: 'Password', type: 'password', required: true, autocomplete: 'password', placeholder: 'Password' },
]);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const handleLogin = async () => {
    form.submit(
        async (fields) => {
            return await login(fields);
        },
        {
            onSuccess: (response: { data: { user: UserResource } }) => {
                userData.value = response.data.user;
                router.push(route.query.redirect ? route.query.redirect.toString() : '/');
            },
            onError: () => form.reset('password'),
        },
    );
};
</script>
<template>
    <BaseForm @submit.prevent="handleLogin">
        <FormItem v-for="(field, index) in fields" :key="index">
            <span v-if="field.name === 'password'" class="flex flex-wrap">
                <FormLabel :for="field.name" :text="field.text" :subtext="field.subtext" class="me-auto" />
                <RouterLink
                    to="/recovery"
                    class="focus:ring-primary-muted rounded-md leading-none text-gray-600 underline hover:text-gray-900 focus:ring-2 focus:ring-offset-2 focus:outline-hidden dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800"
                >
                    Forgot password?
                </RouterLink>
            </span>
            <FormLabel v-else :for="field.name" :text="field.text" :subtext="field.subtext" />
            <FormInput v-model="form.fields[field.name]" :field="field" class="mt-0!" />
            <FormErrorList :errors="form.errors" :field-name="field.name" />
        </FormItem>

        <!-- Remember Me -->
        <label for="remember-me" class="flex w-full items-center gap-2">
            <input
                v-model="form.fields.remember"
                id="remember-me"
                type="checkbox"
                class=""
                :class="[
                    'rounded-sm border-neutral-300 shadow-xs dark:border-neutral-700 dark:bg-neutral-900',
                    'appearance-none',
                    'focus:ring-primary-muted ring-offset-0! focus:ring-2!',
                    'checked:text-primary',
                ]"
                name="remember_me"
            />
            <span class="text-sm text-gray-600 dark:text-gray-400">Remember me</span>
        </label>

        <div class="flex flex-wrap items-center justify-end gap-2 gap-x-4 text-center">
            <RouterLink
                class="focus:ring-primary-muted rounded-md text-gray-600 underline hover:text-gray-900 focus:ring-2 focus:ring-offset-2 focus:outline-hidden dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800"
                to="/register"
            >
                Not Registered?
            </RouterLink>
            <ButtonForm variant="auth" type="button" @click="handleLogin" class="min-h-(--input-height)" :disabled="form.processing"> Log in </ButtonForm>
        </div>
    </BaseForm>
</template>
