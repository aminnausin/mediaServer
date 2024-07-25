<script setup>
    import FormInputLabel from '../components/labels/FormInputLabel.vue';
    import FormInput from '../components/inputs/FormInput.vue';

    import { register } from '../service/auth';
    import { useRouter, RouterLink} from 'vue-router'
    import { useAuthStore } from '../stores/AuthStore';
    import { storeToRefs } from 'pinia';
    import { ref } from 'vue';


    const router = useRouter();
    const authStore = useAuthStore();
    const { userData } = storeToRefs(authStore);

    const registerErrors = ref({});
    const credentials = ref({ name: '', email: '', password: '', password_confirmation: ''});
    const fields = ref([
        {name: 'name', text: 'Name', type:'text', required:true, autocomplete: 'name'},
        {name: 'email', text: 'Email', type:'text', required:true, autocomplete: 'username email'},
        {name: 'password', text: 'Password', type:'password', required:true, autocomplete: 'new-password'},
        {name: 'password_confirmation', text: 'Confirm Password', type:'password', required:true, autocomplete: 'new-password'},
    ]);


    const handleRegister = async () => {
        registerErrors.value = {};

        let { response, error } = await register(credentials.value);

        if(error || !response.success){
            registerErrors.value = { general: error?.message, ...response.errors };
            return;
        }

        localStorage.setItem('auth-token', response.data.token);
        userData.value = response.data.user;
        router.push({name: 'root'});
    }
</script>

<template>
    <main class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0  m-auto bg-gray-100 dark:dark:bg-[#121216] dark:text-[#e2e0e2]">
        <div class=" w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-neutral-800 shadow-md overflow-hidden sm:rounded-lg">
            <form class="flex flex-col gap-4" @submit.prevent="handleRegister">
                <div v-for="(field, index) in fields" :key="index">
                    <FormInputLabel :name="field.name" :text="field.text" />
                    <FormInput v-model="credentials[field.name]" :type="field.type" :name="field.name" :required="field.required" :autocomplete="field.autocomplete"/>
                    <ul class="text-sm text-red-600 dark:text-red-400">
                        <li v-for="(item, index) in registerErrors[field.name]" :key="index">{{item}}</li>
                    </ul>
                </div>

                <div class="flex w-full justify-end">
                    <ul class="text-sm text-red-600 dark:text-red-400">
                        <li>{{registerErrors?.general}}</li>
                    </ul>
                </div>

                <div class="flex items-center justify-end mt-4">
                    <RouterLink class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" to="/login">
                        Already registered?
                    </RouterLink>

                    <button type="submit"  class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 ms-4">
                        Register
                    </button>
                </div>
            </form>
        </div>
    </main>
</template>