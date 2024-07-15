<script setup>
    import { storeToRefs } from 'pinia';
    import { useAuthStore } from '../../stores/AuthStore'
    import { RouterLink } from 'vue-router';

    const authStore = useAuthStore();
    const {user, auth, logout} = storeToRefs(authStore);
</script>

<template>
    <div role="menu" id="user_dropdown" aria-orientation="vertical" aria-labelledby="user_options" class="absolute left-0 z-30 mt-4 w-56 origin-top-right rounded-md shadow-lg ring-1 bg-white ring-black ring-opacity-5 focus:outline-none text-gray-700">
        <div v-if="auth" class="divide-y divide-gray-300" role="menu" id="user-menu-auth">
            <section class="flex flex-wrap gap-1 p-4 justify-between">
                <p class="text-sm leading-5 text-orange-500">Logged in as: </p>
                <p class="text-sm font-medium leading-5 text-gray-900 truncate">{{ user.email }}</p>
            </section>
            <section class="py-1">
                <button class="text-gray-700 flex justify-between w-full px-4 py-2 text-sm leading-5 text-left" role="menuitem">Account settings</button>
                <button class="text-gray-700 flex justify-between w-full px-4 py-2 text-sm leading-5 text-left" role="menuitem">Collections</button>
                <button class="text-gray-700 flex justify-between w-full px-4 py-2 text-sm leading-5 text-left" role="menuitem">Dashboard</button>
                <RouterLink class="text-gray-700 flex justify-between w-full px-4 py-2 text-sm leading-5 text-left font-bold" role="menuitem" to="/history">Full History</RouterLink>
                <button class="text-gray-700 flex justify-between w-full px-4 py-2 text-sm leading-5 text-left" role="menuitem"><a href="/jobs/indexFiles" class="w-full h-full">Index Files</a></button>
                <span role="menuitem" tabindex="-1" class="flex justify-between w-full px-4 py-2 text-sm leading-5 text-left text-gray-700 cursor-not-allowed opacity-50" aria-disabled="true">New feature (soon)</span>
            </section>
            <section class="py-1">
                <button class="text-gray-700 flex justify-between w-full px-4 py-2 text-sm leading-5 text-left" onclick="logout();" role="menuitem">Log out</button>
            </section>
        </div>
        <div v-else role="menu" id="user-menu-unauth" class="">
            <section class="">
                <RouterLink class="rounded-t-md hover:bg-neutral-100 flex justify-between w-full px-4 py-2 text-sm leading-5 text-left" role="menuitem" to="/login">Log in</RouterLink>
                <RouterLink class="rounded-b-md hover:bg-neutral-100 flex justify-between w-full px-4 py-2 text-sm leading-5 text-left" role="menuitem" to="/register">Sign up</RouterLink>
            </section>
        </div>
    </div>
</template>