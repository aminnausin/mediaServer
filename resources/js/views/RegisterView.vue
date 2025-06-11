<script setup>
import { useRouter, RouterLink } from 'vue-router';
import { useAuthStore } from '@/stores/AuthStore';
import { storeToRefs } from 'pinia';
import { register } from '@/service/authAPI';
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

const fields = ref([
    { name: 'name', text: 'Name', type: 'text', required: true, autocomplete: 'name' },
    { name: 'email', text: 'Email', type: 'text', required: true, autocomplete: 'username email' },
    { name: 'password', text: 'Password', type: 'password', required: true, autocomplete: 'new-password' },
    { name: 'password_confirmation', text: 'Confirm Password', type: 'password', required: true, autocomplete: 'new-password' },
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
    <LayoutAuth>
        <AuthHeader>Enter your details below to create your account</AuthHeader>
        <AuthCard>
            <BaseForm @submit.prevent="handleRegister">
                <FormItem v-for="(field, index) in fields" :key="index">
                    <FormInputLabel :field="field" />
                    <FormInput v-model="form.fields[field.name]" :field="field" class="!mt-0" />
                    <FormErrorList :errors="form.errors" :field-name="field.name" />
                </FormItem>
                <div class="flex flex-wrap gap-2 gap-x-4 items-center justify-center sm:justify-end text-center">
                    <RouterLink
                        class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                        to="/login"
                    >
                        Already registered?
                    </RouterLink>

                    <ButtonForm variant="auth" type="submit" :disabled="form.processing">Register</ButtonForm>
                </div>
            </BaseForm>
        </AuthCard>
    </LayoutAuth>
</template>
