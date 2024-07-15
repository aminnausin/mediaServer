<script setup>
    import { storeToRefs } from 'pinia';
    import { useAuthStore } from '../../stores/AuthStore'
    import { ref, onMounted } from 'vue';
    import UserDropdown from '../user/UserDropdown.vue';

    const authStore = useAuthStore();
    const { user, auth, pageTitle, darkMode } = storeToRefs(authStore);
    const showDropdown = ref(false);

    const { toggleDarkMode } = authStore;

    const toggleDropdown = () => {
        showDropdown.value = !showDropdown.value;
        console.log(showDropdown.value);
    }

    window.addEventListener('click', function(e){
        try {
            if (!this.document.querySelector("#user_options").contains(e.target)){
                showDropdown.value = false;
            } 
        } catch (error) {
            console.log(error);
        }
    });


    onMounted(() => {
        toggleDarkMode(true);
    })
</script>

<template>
    <nav id="navbar">
        <div class="flex p-1 gap-y-3 flex-wrap justify-between">
            <h1 id="title" class="text-2xl">{{ pageTitle }}</h1>
            <span class="flex flex-wrap sm:flex-nowrap sm:max-w-sm items-center gap-2  sm:shrink-0">
                <section id="user_options" class="group inline-block relative" data-dropdown-toggle="user_dropdown" aria-haspopup="true">
                    <button id="user_header" class="flex space-x-2 text-2xl text-slate-900 dark:text-white hover:text-orange-600 dark:hover:text-orange-600 items-center justify-center" @click="toggleDropdown">
                        
                        <span id="user_name" v-if="auth">{{ user.username }}</span>
                        <span id="user_name_unauth" v-else class="w-[10vw] text-right">Guest</span>
                        
                        <img :src="user.avatar" class="h-7 w-7 rounded-full sm:mx-0 sm:shrink-0 ring-2 ring-orange-600/60 shadow-lg object-cover" alt="profile picture">
                    </button>    
                    <UserDropdown v-if="showDropdown"/>
                </section>

                <section id="navbar-video" class="flex items-center space-x-2">
                    <button id="btn-nav-folders" class="flex justify-center items-center shrink-0 h-8 w-8 rounded-lg shadow-lg bg-red-300 hover:bg-red-200" aria-label="folders">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" height="24" width="24" class="stroke-slate-900">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 0 0-1.883 2.542l.857 6a2.25 2.25 0 0 0 2.227 1.932H19.05a2.25 2.25 0 0 0 2.227-1.932l.857-6a2.25 2.25 0 0 0-1.883-2.542m-16.5 0V6A2.25 2.25 0 0 1 6 3.75h3.879a1.5 1.5 0 0 1 1.06.44l2.122 2.12a1.5 1.5 0 0 0 1.06.44H18A2.25 2.25 0 0 1 20.25 9v.776" />
                        </svg>
                    </button>
                    <button v-if="auth" id="btn-nav-history" class="flex justify-center items-center shrink-0 h-8 w-8 rounded-lg shadow-lg bg-purple-300 hover:bg-purple-200" aria-label="watch history">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" height="24" width="24" class="fill-slate-900">
                            <path d="M0 0h24v24H0z" fill="none" />
                            <path fill-rule="evenodd" d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z" />
                        </svg>
                    </button>
                    <button v-if="auth" id="btn-nav-settings" class=" hidden flex justify-center items-center shrink-0 h-8 w-8 rounded-lg shadow-lg bg-red-300 hover:bg-red-200" aria-label="settings">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" height="24" width="24" class="fill-slate-900">
                            <path fill-rule="evenodd" d="M3 6.75A.75.75 0 0 1 3.75 6h16.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 6.75ZM3 12a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 12Zm0 5.25a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 0 1.5H3.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </section>

                <div class="toggle-switch shrink-0">
                    <label class="switch-label">
                        <input type="checkbox" class="checkbox invisible" id="dark-mode-toggle" @click="toggleDarkMode(false)" v-bind:checked="!darkMode">
                        <span class="slider"></span>
                    </label>
                </div>
            </span>
        </div>
        <hr class="mt-2 mb-3">
    </nav>
</template>