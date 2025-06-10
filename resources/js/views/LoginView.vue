<script setup lang="ts">
import type { FormField } from '@/types/types';

import { useRouter, useRoute, RouterLink } from 'vue-router';
import { useAuthStore } from '@/stores/AuthStore';
import { storeToRefs } from 'pinia';
import { login } from '@/service/authAPI';
import { ref } from 'vue';

import FormInputLabel from '@/components/labels/FormInputLabel.vue';
import FormErrorList from '@/components/labels/FormErrorList.vue';
import LayoutAuth from '@/layouts/LayoutAuth.vue';
import FormInput from '@/components/inputs/FormInput.vue';
import useForm from '@/composables/useForm';

const { userData } = storeToRefs(useAuthStore());
const router = useRouter();
const route = useRoute();

const fields = ref<FormField[]>([
    { name: 'email', text: 'Email', type: 'text', required: true, autocomplete: 'username email' },
    { name: 'password', text: 'Password', type: 'password', required: true, autocomplete: 'password' },
]);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const handleLogin = async () => {
    form.submit(
        async (fields: any) => {
            return await login(fields);
        },
        {
            onSuccess: (response: { data: { data: { token: string; user: null } } }) => {
                localStorage.setItem('auth-token', response.data.data.token);
                userData.value = response.data.data.user;
                router.push(route.query.redirect ? route.query.redirect.toString() : '/');
            },
            onError: (errors: any) => form.reset('password'),
        },
    );
};
</script>

<template>
    <LayoutAuth class="text-sm">
        <template #content>
            <div class="flex items-center pt-8 sm:justify-start sm:pt-0 text-gray-500 border-gray-400 dark:text-gray-400 dark:border-gray-400">
                <div class="px-4 text-lg tracking-wider">Media Server</div>
            </div>
            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-neutral-800 sm:shadow-md overflow-hidden sm:rounded-lg">
                <!-- Session Status -->
                <form class="flex flex-col gap-2" @submit.prevent="handleLogin">
                    <div v-for="(field, index) in fields" :key="index">
                        <span v-if="field.name === 'password'" class="flex flex-wrap">
                            <FormInputLabel :field="field" class="me-auto" />
                            <RouterLink
                                to="/recovery"
                                class="underline text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                            >
                                Forgot password?
                            </RouterLink>
                        </span>
                        <FormInputLabel v-else :field="field" />
                        <FormInput v-model="form.fields[field.name]" :field="field" />
                    </div>

                    <!-- Remember Me -->
                    <div class="block">
                        <label for="remember-me" class="inline-flex items-center">
                            <input
                                v-model="form.fields.remember"
                                id="remember-me"
                                type="checkbox"
                                class=""
                                :class="[
                                    'rounded dark:bg-neutral-900 border-neutral-300 dark:border-neutral-700 shadow-sm',
                                    'appearance-none',
                                    'focus:ring-indigo-500 focus:!ring-[0.125rem] !ring-offset-0',
                                    'checked:text-indigo-600',
                                ]"
                                name="remember_me"
                            />
                            <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Remember me</span>
                        </label>
                    </div>

                    <div class="flex w-full justify-end">
                        <FormErrorList>
                            <li v-for="(error, index) in form.errors" :key="index">{{ error }}</li>
                        </FormErrorList>
                    </div>

                    <div class="flex items-center justify-center gap-y-2 sm:justify-end mt-4 flex-wrap text-center">
                        <RouterLink
                            class="underline text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                            to="/register"
                        >
                            Not Registered?
                        </RouterLink>

                        <button
                            type="submit"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 ms-3"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Log in
                        </button>
                    </div>
                </form>
            </div>
        </template>
    </LayoutAuth>
</template>
