<!-- <script setup>
const props = defineProps({
    user: Object
});
</script> -->
<script setup>
    import { onMounted, ref } from 'vue';
    import {RouterView} from 'vue-router'
    import { useAuthStore } from './stores/AuthStore';
    const authStore = useAuthStore();

    onMounted(() => {
        fetch(`/sanctum/csrf-cookie`, {
            method: 'get'
        }).catch((error) => {
            console.log(error);
        });
    })
</script>

<template>
    <RouterView/>
</template>