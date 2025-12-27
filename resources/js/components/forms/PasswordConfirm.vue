<script setup lang="ts">
import type { PasswordRequest } from '@/types/requests';
import type { AxiosResponse } from 'axios';
import type { FormField } from '@/types/types';

import { toast } from '@aminnausin/cedar-ui';

import { FormLabel } from '@/components/cedar-ui/form';
import FormErrorList from '@/components/labels/FormErrorList.vue';
import ButtonForm from '@/components/inputs/ButtonForm.vue';
import FormInput from '@/components/inputs/FormInput.vue';
import BaseForm from '@/components/forms/BaseForm.vue';
import FormItem from '@/components/forms/FormItem.vue';
import useForm from '@/composables/useForm';

const emit = defineEmits(['confirm', 'cancel']);

const props = withDefaults(
    defineProps<{
        action: (fields: PasswordRequest) => Promise<AxiosResponse<any>>;
        successAction?: (response: AxiosResponse<any>) => void;
        successMessage?: string;
        confirmText?: string;
        cancelText?: string;
        passwordLabel?: string;
    }>(),
    {
        confirmText: 'Confirm',
        cancelText: 'Cancel',
    },
);

const fields: FormField[] = [
    {
        name: 'password',
        text: 'Confirm Password',
        placeholder: 'Password',
        autocomplete: 'current-password',
        ariaAutocomplete: 'inline',
        type: 'password',
        required: true,
        max: 255,
    },
];

const form = useForm<PasswordRequest>({
    password: '',
});

const handleSubmit = async () => {
    form.submit(
        async (fields) => {
            return props.action(fields);
        },
        {
            onSuccess: (response) => {
                if (props.successMessage) toast.success(props.successMessage);
                if (props.successAction) props.successAction(response);
                emit('confirm');
            },
            onError() {
                form.reset('password');
            },
        },
    );
};
</script>
<template>
    <BaseForm @submit.prevent="handleSubmit" :footer-class="`flex flex-col-reverse sm:flex-row sm:justify-end gap-2 text-sm`">
        <FormItem v-for="(field, index) in fields" :key="index">
            <FormLabel v-if="passwordLabel" :for="field.name" :text="field.text" :subtext="field.subtext" />
            <FormInput v-model="form.fields[field.name]" :field="field" class="mt-0!" />
            <FormErrorList :errors="form.errors" :field-name="field.name" />
        </FormItem>

        <template #footer>
            <ButtonForm variant="reset" type="button" :disabled="form.processing" class="capitalize!" @click="$emit('cancel')">{{ cancelText }}</ButtonForm>
            <ButtonForm variant="submit" type="button" @click="handleSubmit" :disabled="form.processing" class="bg-danger-2! capitalize! hover:bg-rose-500!">
                {{ confirmText }}
            </ButtonForm>
        </template>
    </BaseForm>
</template>
