<script setup lang="ts">
import type { FormField } from '@/types/types';

import { recoverAccount } from '@/service/authAPI';
import { RouterLink } from 'vue-router';
import { toast } from '@/service/toaster/toastService';
import { ref } from 'vue';

import FormInputLabel from '@/components/labels/FormInputLabel.vue';
import FormErrorList from '@/components/labels/FormErrorList.vue';
import ButtonForm from '@/components/inputs/ButtonForm.vue';
import LayoutAuth from '@/layouts/LayoutAuth.vue';
import FormInput from '@/components/inputs/FormInput.vue';
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
    <LayoutAuth class="text-sm">
        <template #content>
            <div class="flex items-center pt-8 px-6 text-center sm:justify-start sm:pt-0 flex-col gap-1">
                <h1 class="px-4 text-2xl">Media Server</h1>
                <p class="text-neutral-500 dark:text-neutral-400">Enter your email to receive a password reset link</p>
            </div>
            <div class="flex flex-col w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-neutral-800 sm:shadow-md overflow-hidden sm:rounded-lg gap-4">
                <form class="flex flex-col gap-4" @submit.prevent="handleSubmit">
                    <div v-for="(field, index) in fields" :key="index">
                        <FormInputLabel :field="field" />
                        <FormInput v-model="form.fields[field.name]" :field="field" />
                        <FormErrorList>
                            <li v-for="(item, index) in form.errors[field.name]" :key="index">{{ item }}</li>
                        </FormErrorList>
                    </div>

                    <ButtonForm
                        variant="submit"
                        type="button"
                        @click="handleSubmit"
                        :disabled="form.processing"
                        :class="`${form.processing ? 'opacity-25 ' : ''}h-8 font-semibold text-xs uppercase tracking-widest transition ease-in-out duration-150`"
                        >Email password reset link
                    </ButtonForm>
                </form>
                <span class="mx-auto">
                    Or, return to
                    <RouterLink
                        class="underline text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                        to="/login"
                    >
                        log in
                    </RouterLink>
                </span>
            </div>
        </template>
    </LayoutAuth>
</template>
