<script setup lang="ts">
import type { ChangeEmailRequest } from '@/types/requests';
import type { FormField } from '@/types/types';

import { FormInput, FormLabel, FormErrorList } from '@/components/cedar-ui/form';
import { changeEmail } from '@/service/authAPI';
import { ButtonForm } from '@/components/cedar-ui/button';
import { reactive } from 'vue';
import { toast } from '@aminnausin/cedar-ui';

import SettingsHeader from '@/components/settings/SettingsHeader.vue';
import SettingsCard from '@/components/cards/layout/SettingsCard.vue';
import useForm from '@/composables/useForm';

const fields = reactive<FormField[]>([
    {
        name: 'email',
        text: `New Email`,
        autocomplete: 'email',
        ariaAutocomplete: 'inline',
        type: 'text',
        required: true,
        placeholder: `New Email`,
        max: 255,
    },
    {
        name: 'password',
        text: 'Confirm Password',
        placeholder: 'Confirm Password',
        autocomplete: 'current-password',
        ariaAutocomplete: 'inline',
        type: 'password',
        required: true,
        max: 255,
    },
]);

const form = useForm<ChangeEmailRequest>({
    email: '',
    password: '',
});

const handleSubmit = async () => {
    form.submit(
        async (fields) => {
            return changeEmail(fields);
        },
        {
            onSuccess: () => {
                toast.add('Success', { type: 'success', description: 'Email Changed', life: 3000 });
                form.reset(...Object.keys(form.fields));
            },
            onError: () => {
                form.reset('password');

                if (form.errors.email) {
                    form.reset('email');
                    return;
                }
                toast.add('Error', { type: 'danger', description: 'Unable change email.', life: 3000 });
            },
        },
    );
};
</script>

<template>
    <SettingsCard>
        <template #content>
            <SettingsHeader>
                <h3 class="text-base font-medium">Update Email</h3>
                <p class="text-foreground-1">Ensure you have secure access to this email.</p>
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
