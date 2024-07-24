<script setup>
    import { onMounted } from 'vue';
    import {RouterView} from 'vue-router'
    import { useAuthStore } from './stores/AuthStore';
    const authStore = useAuthStore();
    
    const { auth, toggleDarkMode } = authStore;

    onMounted(async () => {
        fetch(`/sanctum/csrf-cookie`, {
            method: 'get'
        }).catch((error) => {
            console.log(error);
        });
        toggleDarkMode(true);

        // await auth();
    })
</script>

<template>
    <RouterView/>
</template>