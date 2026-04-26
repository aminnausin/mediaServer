<script setup lang="ts">
import type { ChangePasswordRequest } from '@/types/requests';
import type { FormField } from '@/types/types';

import { FormInput, FormLabel, FormErrorList } from '@/components/cedar-ui/form';
import { changePassword } from '@/service/authAPI';
import { ButtonForm } from '@/components/cedar-ui/button';
import { reactive } from 'vue';
import { toast } from '@aminnausin/cedar-ui';

import SettingsHeader from '@/components/settings/SettingsHeader.vue';
import SettingsCard from '@/components/cards/layout/SettingsCard.vue';
import useForm from '@/composables/useForm';

// To avoid having duplicate inputs with the same id on the account settings page
type ChangePasswordFormValues = {
    current_password: string;
    new_password: string;
    password_confirmation: string;
};

const fields = reactive<FormField[]>([
    {
        name: 'current_password',
        text: 'Current Password',
        placeholder: 'Current Password',
        autocomplete: 'current-password',
        ariaAutocomplete: 'inline',
        type: 'password',
        required: true,
        max: 255,
    },
    {
        name: 'new_password',
        text: `New Password`,
        placeholder: `New Password`,
        autocomplete: 'new-password',
        type: 'password',
        required: true,
        max: 255,
    },
    {
        name: 'password_confirmation',
        text: `Confirm Password`,
        placeholder: `Confirm Password`,
        type: 'password',
        required: true,
        max: 255,
    },
]);

const form = useForm<ChangePasswordFormValues>({
    current_password: '',
    new_password: '',
    password_confirmation: '',
});

const mapToChangePasswordRequest = (fields: ChangePasswordFormValues): ChangePasswordRequest => {
    return {
        current_password: fields.current_password,
        password: fields.new_password,
        password_confirmation: fields.password_confirmation,
    };
};

const handleSubmit = async () => {
    form.submit(
        async (fields) => {
            return changePassword(mapToChangePasswordRequest(fields));
        },
        {
            onSuccess: (response) => {
                toast.add('Success', { type: 'success', description: 'Password Changed', life: 3000 });
                form.reset(...Object.keys(form.fields));
            },
            onError: () => {
                if (form.errors.password) {
                    form.reset('new_password', 'password_confirmation');
                    return;
                }

                if (form.errors.current_password) {
                    form.reset('current_password');
                    return;
                }

                toast.add('Error', { type: 'danger', description: 'Unable change password.', life: 3000 });
            },
        },
    );
};
</script>

<template>
    <SettingsCard>
        <template #content>
            <SettingsHeader>
                <h3 class="text-base font-medium">Update password</h3>
                <p class="text-foreground-1">Ensure your account is using a long, random password to stay secure.</p>
            </SettingsHeader>
            <form class="flex w-full max-w-xl flex-col flex-wrap gap-4 sm:flex-row sm:justify-between" @submit.prevent="handleSubmit">
                <div v-for="(field, index) in fields.filter((field) => !field.disabled)" :key="index" class="w-full" :class="field.class">
                    <FormLabel :for="field.name" :text="field.text" :subtext="field.subtext" />
                    <FormInput v-model="form.fields[field.name]" :field="field" :class="'dark:bg-primary-dark-900/70 bg-white ring-neutral-300 dark:ring-neutral-800'" />
                    <FormErrorList :errors="form.errors" :field-name="field.name" />
                </div>

                <div class="relative flex w-full flex-col-reverse gap-2 *:h-8 sm:flex-row sm:justify-end">
                    <ButtonForm @click="form.reset(...Object.keys(form.fields))" type="button" variant="reset" :disabled="form.processing"> Cancel </ButtonForm>
                    <ButtonForm @click="handleSubmit" type="button" variant="submit" :disabled="form.processing"> Save Email </ButtonForm>
                </div>
            </form>
        </template>
    </SettingsCard>
</template>
