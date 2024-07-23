<script setup>
    import { storeToRefs } from 'pinia';
    import { useAuthStore } from '../stores/AuthStore'
    import { ref } from 'vue';
    import { login } from '../service/auth';
    import { useRouter, useRoute } from 'vue-router'

    const router = useRouter()
    const route = useRoute()
    const authStore = useAuthStore();
    const {csrfToken, userData} = storeToRefs(authStore);

    // const { login } = authStore;


    const loginError = ref('')
    const credentials = ref({email: '', password: '', remember: false, _token: csrfToken});

    const handleLogin = async (e) => {
        e?.preventDefault();
        loginError.value = '';

        let { response, error} = await login(credentials.value);

        if(error || response.success === false){
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
            <form>
                <input type="hidden" name="_token" :value="csrfToken" autocomplete="off">
                <!-- Email Address -->
                <div>
                    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" for="email">
                        Email
                    </label>
                    <input v-model="credentials.email" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full" id="email" type="email" name="email" required="required" autofocus="autofocus" autocomplete="username">
                    <ul class="text-sm text-red-600 dark:text-red-400 space-y-1 mt-2">
                        <li>{{loginError}}</li>
                    </ul>
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" for="password">
                        Password
                    </label>

                    <input v-model="credentials.password" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full" id="password" type="password" name="password" required="required" autocomplete="current-password">
                </div>

                <!-- Remember Me -->
                <div class="block mt-4">
                    <label for="remember_me" class="inline-flex items-center">
                        <input v-model="credentials.remember" id="remember_me" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="remember_me">
                        <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Remember me</span>
                    </label>
                </div>

                <div class="flex items-center justify-end mt-4">
                    <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="/register">
                        Forgot your password?
                    </a>

                    <button @click="handleLogin" type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 ms-3">
                        Log in
                    </button>
                </div>
            </form>
        </div>
    </main>
</template>