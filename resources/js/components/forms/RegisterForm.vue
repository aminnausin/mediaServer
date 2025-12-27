<script setup lang="ts">
import type { FormField } from '@/types/types';

import { useRouter, RouterLink } from 'vue-router';
import { useAuthStore } from '@/stores/AuthStore';
import { storeToRefs } from 'pinia';
import { FormLabel } from '@/components/cedar-ui/form';
import { register } from '@/service/authAPI';
import { ref } from 'vue';

import FormErrorList from '@/components/labels/FormErrorList.vue';
import ButtonForm from '@/components/inputs/ButtonForm.vue';
import FormInput from '@/components/inputs/FormInput.vue';
import BaseForm from '@/components/forms/BaseForm.vue';
import FormItem from '@/components/forms/FormItem.vue';
import useForm from '@/composables/useForm';

const { userData } = storeToRefs(useAuthStore());

const router = useRouter();

const fields = ref<FormField[]>([
    { name: 'name', text: 'Name', type: 'text', required: true, autocomplete: 'name', placeholder: 'Name' },
    { name: 'email', text: 'Email', type: 'text', required: true, autocomplete: 'username email', placeholder: 'Email' },
    { name: 'password', text: 'Password', type: 'password', required: true, autocomplete: 'new-password', placeholder: 'Password' },
    { name: 'password_confirmation', text: 'Confirm Password', type: 'password', required: true, autocomplete: 'new-password', placeholder: 'Confirm Password' },
]);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const handleRegister = async () => {
    form.submit(
        async (fields) => {
            return await register(fields);
        },
        {
            onSuccess: (response) => {
                localStorage.setItem('auth-token', response.data.token);
                userData.value = response.data.user;
                router.push({ name: 'root' });
            },
            onError: () => {
                form.reset('password', 'password_confirmation');
            },
        },
    );
};
</script>

<template>
    <BaseForm @submit.prevent="handleRegister">
        <FormItem v-for="(field, index) in fields" :key="index">
            <FormLabel :for="field.name" :text="field.text" :subtext="field.subtext" />
            <FormInput v-model="form.fields[field.name]" :field="field" class="mt-0!" />
            <FormErrorList :errors="form.errors" :field-name="field.name" />
        </FormItem>
        <div class="flex flex-wrap items-center justify-end gap-2 gap-x-4 text-center">
            <RouterLink
                class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-hidden dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800"
                to="/login"
            >
                Already registered?
            </RouterLink>

            <ButtonForm variant="auth" type="submit" :disabled="form.processing">Register</ButtonForm>
        </div>
    </BaseForm>
</template>
