<script setup>
    import TextInputLabel from '../components/labels/TextInputLabel.vue';
    import TextInput from '../components/inputs/TextInput.vue';

    import { useRouter, useRoute, RouterLink } from 'vue-router'
    import { useAuthStore } from '../stores/AuthStore'
    import { storeToRefs } from 'pinia';
    import { login } from '../service/auth';
    import { ref } from 'vue';


    const router = useRouter();
    const route = useRoute();
    const authStore = useAuthStore();
    const { userData } = storeToRefs(authStore);

    const loginError = ref('')
    const credentials = ref({email: '', password: '', remember: false});
    const fields = ref([
        {name: 'email', text: 'Email', type:'text', required:true, autocomplete: 'username email'},
        {name: 'password', text: 'Password', type:'password', required:true, autocomplete: 'password'},
    ]);

    
    const handleLogin = async () => {
        loginError.value = '';

        // await axios.get(`/sanctum/csrf-cookie`);
        // axios.post('/api/login', credentials.value)


        let { response, error } = await login(credentials.value);

        if(error || !response.success){
            loginError.value = error ? error.message : response.message;
            return;
        }
        
        localStorage.setItem('auth-token', response.data.token);
        userData.value = response.data.user;
        router.replace(route.query.redirect || '/');
    }
</script>

<template>
    <main class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0  m-auto bg-gray-100 dark:dark:bg-[#121216] dark:text-[#e2e0e2]">
        <div class=" w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-neutral-800 shadow-md overflow-hidden sm:rounded-lg">
            <!-- Session Status -->
            <form class="flex flex-col gap-2" @submit.prevent="handleLogin">
                <div v-for="(field, index) in fields" :key="index">
                    <TextInputLabel :name="field.name" :text="field.text" />
                    <TextInput v-model="credentials[field.name]" :type="field.type" :name="field.name" :required="field.required" :autocomplete="field.autocomplete"/>
                </div>

                <!-- Remember Me -->
                <div class="block">
                    <label for="remember_me" class="inline-flex items-center">
                        <input v-model="credentials.remember" id="remember_me" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="remember_me">
                        <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Remember me</span>
                    </label>
                </div>

                <div class="flex w-full justify-end">
                    <ul class="text-sm text-red-600 dark:text-red-400">
                        <li>{{loginError}}</li>
                    </ul>
                </div>

                <div class="flex items-center justify-end mt-4">
                    <RouterLink class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" to="/register">
                        Not Registered?
                    </RouterLink>

                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 ms-3">
                        Log in
                    </button>
                </div>
            </form>
        </div>
    </main>
</template>