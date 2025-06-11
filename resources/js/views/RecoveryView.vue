<script setup lang="ts">
import type { FormField } from '@/types/types';

import { recoverAccount } from '@/service/authAPI';
import { RouterLink } from 'vue-router';
import { toast } from '@/service/toaster/toastService';
import { ref } from 'vue';

import FormInputLabel from '@/components/labels/FormInputLabel.vue';
import FormErrorList from '@/components/labels/FormErrorList.vue';
import AuthHeader from '@/components/headers/AuthHeader.vue';
import ButtonForm from '@/components/inputs/ButtonForm.vue';
import LayoutAuth from '@/layouts/LayoutAuth.vue';
import FormInput from '@/components/inputs/FormInput.vue';
import AuthCard from '@/components/cards/AuthCard.vue';
import useForm from '@/composables/useForm';
import BaseForm from '@/components/forms/BaseForm.vue';
import FormItem from '@/components/forms/FormItem.vue';

const fields = ref<FormField[]>([{ name: 'email', text: 'Email', type: 'text', required: true, autocomplete: 'email', placeholder: 'email@example.ca' }]);

const form = useForm<{ email: string }>({
    email: '',
});

const handleSubmit = async () => {
    form.submit(
        async (fields) => {
            return recoverAccount(fields);
        },
        {
            onSuccess: (response) => {
                toast.success('A reset link will be sent if the account exists.', { description: 'This is not yet implemented.' });
                form.reset('email');
            },
            onError() {
                form.reset('email');
            },
        },
    );
};
</script>

<template>
    <LayoutAuth>
        <AuthHeader> Enter your email to receive a password reset link </AuthHeader>
        <AuthCard>
            <BaseForm @submit.prevent="handleSubmit">
                <FormItem v-for="(field, index) in fields" :key="index">
                    <FormInputLabel :field="field" />
                    <FormInput v-model="form.fields[field.name]" :field="field" class="!mt-0" />
                    <FormErrorList :errors="form.errors" :field-name="field.name" />
                </FormItem>

                <ButtonForm variant="auth" type="button" @click="handleSubmit" :disabled="form.processing" class="!justify-center !capitalize">
                    Email password reset link
                </ButtonForm>
            </BaseForm>
            <span class="mx-auto text-gray-600 dark:text-gray-400">
                Or, return to
                <RouterLink
                    class="underline hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                    to="/login"
                >
                    log in
                </RouterLink>
            </span>
        </AuthCard>
    </LayoutAuth>
</template>
