<script setup lang="ts">
import { useRouter, useRoute, RouterLink } from 'vue-router';
import { useAuthStore } from '@/stores/AuthStore';
import { storeToRefs } from 'pinia';
import { login } from '@/service/authAPI';
import { ref } from 'vue';

import FormInputLabel from '@/components/labels/FormInputLabel.vue';
import LayoutAuth from '@/layouts/LayoutAuth.vue';
import FormInput from '@/components/inputs/FormInput.vue';
import useForm from '@/composables/useForm';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const { userData } = storeToRefs(authStore);

const fields = ref([
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
            console.log(fields);

            return await login(fields);
        },
        {
            onSuccess: (response: { data: { data: { token: string; user: null } } }) => {
                localStorage.setItem('auth-token', response.data.data.token);
                userData.value = response.data.data.user;
                router.push(route.query.redirect ? route.query.redirect.toString() : '/');
            },
            onError: () => form.reset('password'),
        },
    );
};
</script>

<template>
    <LayoutAuth>
        <template #content>
            <div
                class="flex items-center pt-8 sm:justify-start sm:pt-0 text-gray-500 border-gray-400 dark:text-gray-400 dark:border-gray-400"
            >
                <div class="px-4 text-lg tracking-wider">Media Server</div>
            </div>
            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-neutral-800 sm:shadow-md overflow-hidden sm:rounded-lg">
                <!-- Session Status -->
                <form class="flex flex-col gap-2" @submit.prevent="handleLogin">
                    <div v-for="(field, index) in fields" :key="index">
                        <FormInputLabel :field="field" />
                        <FormInput v-model="form.fields[field.name]" :field="field" />
                    </div>

                    <!-- Remember Me -->
                    <div class="block">
                        <label for="remember_me" class="inline-flex items-center">
                            <input
                                v-model="form.fields.remember"
                                id="remember_me"
                                type="checkbox"
                                class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800"
                                name="remember_me"
                            />
                            <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Remember me</span>
                        </label>
                    </div>

                    <div class="flex w-full justify-end">
                        <ul class="text-sm text-red-600 dark:text-red-400">
                            <li v-for="(error, index) in form.errors" :key="index">{{ error }}</li>
                        </ul>
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <RouterLink
                            class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
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
