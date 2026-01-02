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
import { InputShell } from '../cedar-ui/input';
import { cn } from '@aminnausin/cedar-ui';

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
                <RouterLink to="/recovery" class="focus:ring-primary-muted text-foreground-1 hover:text-foreground-0 rounded-md underline focus:ring-2 focus:outline-hidden">
                    Forgot password?
                </RouterLink>
            </span>
            <FormLabel v-else :for="field.name" :text="field.text" :subtext="field.subtext" />
            <FormInput v-model="form.fields[field.name]" :field="field" class="mt-0!" />
            <FormErrorList :errors="form.errors" :field-name="field.name" />
        </FormItem>

        <!-- Remember Me -->
        <div class="flex items-center gap-2">
            <InputShell>
                <template #input="{ class: inputClass }">
                    <input
                        :class="cn(inputClass, 'checked:bg-primary focus:ring-primary-muted! size-4 cursor-pointer rounded-sm shadow-xs', 'ring-offset-0')"
                        id="remember-me"
                        type="checkbox"
                        name="remember_me"
                    />
                </template>
            </InputShell>
            <FormLabel for="remember-me" class="text-foreground-1 text-sm"> Remember me </FormLabel>
        </div>
        <div class="flex flex-wrap items-center justify-end gap-2 gap-x-4 text-center">
            <RouterLink class="focus:ring-primary-muted text-foreground-1 hover:text-foreground-0 rounded-md underline focus:ring-2 focus:outline-hidden" to="/register">
                Not Registered?
            </RouterLink>
            <ButtonForm variant="auth" type="button" @click="handleLogin" class="min-h-(--input-height)" :disabled="form.processing"> Log in </ButtonForm>
        </div>
    </BaseForm>
</template>
