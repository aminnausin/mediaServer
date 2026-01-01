<script setup lang="ts">
import type { FormField } from '@/types/types';

import { FormInput, FormLabel, FormErrorList } from '@/components/cedar-ui/form';
import { recoverAccount } from '@/service/authAPI';
import { ButtonForm } from '@/components/cedar-ui/button';
import { RouterLink } from 'vue-router';
import { toast } from '@aminnausin/cedar-ui';
import { ref } from 'vue';

import BaseForm from '@/components/forms/BaseForm.vue';
import FormItem from '@/components/forms/FormItem.vue';
import useForm from '@/composables/useForm';

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
                toast.success('A reset link will be sent if the account exists.');
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
    <BaseForm @submit.prevent="handleSubmit">
        <FormItem v-for="(field, index) in fields" :key="index">
            <FormLabel :for="field.name" :text="field.text" :subtext="field.subtext" />
            <FormInput v-model="form.fields[field.name]" :field="field" class="mt-0!" />
            <FormErrorList :errors="form.errors" :field-name="field.name" />
        </FormItem>

        <ButtonForm variant="auth" type="button" @click="handleSubmit" :disabled="form.processing" class="min-h-(--input-height) capitalize">Email password reset link</ButtonForm>
    </BaseForm>
    <span class="text-foreground-1 mx-auto">
        Or, return to
        <RouterLink class="focus:ring-primary-muted hover:text-foreground-0 rounded-md underline focus:ring-2 focus:outline-hidden" to="/login"> log in </RouterLink>
    </span>
</template>
