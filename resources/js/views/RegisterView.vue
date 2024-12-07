<script setup>
import { useRouter, RouterLink } from 'vue-router';
import { useAuthStore } from '@/stores/AuthStore';
import { storeToRefs } from 'pinia';
import { register } from '@/service/authAPI';
import { ref } from 'vue';

import FormInputLabel from '@/components/labels/FormInputLabel.vue';
import LayoutAuth from '@/layouts/LayoutAuth.vue';
import FormInput from '@/components/inputs/FormInput.vue';
import useForm from '@/composables/useForm';

const router = useRouter();
const authStore = useAuthStore();
const { userData } = storeToRefs(authStore);

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
        <template #content>
            <div
                class="flex items-center pt-8 sm:justify-start sm:pt-0 text-gray-500 border-gray-400 dark:text-gray-400 dark:border-gray-400"
            >
                <div class="px-4 text-lg tracking-wider">Media Server</div>
            </div>
            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-neutral-800 sm:shadow-md overflow-hidden sm:rounded-lg">
                <form class="flex flex-col gap-4" @submit.prevent="handleRegister">
                    <div v-for="(field, index) in fields" :key="index">
                        <FormInputLabel :field="field" />
                        <FormInput v-model="form.fields[field.name]" :field="field" />
                        <ul class="text-sm text-red-600 dark:text-red-400">
                            <li v-for="(item, index) in form.errors[field.name]" :key="index">{{ item }}</li>
                        </ul>
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <RouterLink
                            class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                            to="/login"
                        >
                            Already registered?
                        </RouterLink>

                        <button
                            type="submit"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 ms-4"
                        >
                            Register
                        </button>
                    </div>
                </form>
            </div>
        </template>
    </LayoutAuth>
</template>
