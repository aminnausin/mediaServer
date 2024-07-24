<script setup>
    import { useAuthStore } from '../../stores/AuthStore'
    import { storeToRefs } from 'pinia';
    import { RouterLink } from 'vue-router';
    import DropdownLink from './DropdownLink.vue';

    
    const authStore = useAuthStore();
    const { userData } = storeToRefs(authStore);


    const dropDownItems = [
        {name:'login', url:'/login', text:'Log in'}, 
        {name:'register', url:'/register', text:'Sign up'}
    ];
    const dropDownItemsAuth = [
        {name:'account', url:'/account', text:'Account settings'}, 
        {name:'collections', url:'/collections', text:'Collections'},
        {name:'dashboard', url:'/Dashboard', text:'Dashboard'},
        {name:'history', url:'/history', text:'Full History'},
        {name:'index', url:'/jobs/indexFiles', text:'Index Files', external: true},
        {name:'sync', url:'/jobs/syncFiles', text:'Sync Files', external: true},
    ];
</script>

<template>
    <div role="menu" id="user_dropdown" aria-orientation="vertical" aria-labelledby="user_options" class="absolute left-0 z-30 mt-4 w-56 origin-top-right rounded-md shadow-lg ring-1 bg-white ring-black ring-opacity-5 focus:outline-none text-gray-700">
        <div v-if="userData" class="divide-y divide-gray-300" role="menu" id="user-menu-auth">
            <section class="flex flex-wrap gap-1 p-4 justify-between">
                <p class="text-sm leading-5 text-orange-500">Logged in as: </p>
                <p class="text-sm font-medium leading-5 text-gray-900 truncate">{{ userData.email }}</p>
            </section>
            <section class="py-1">
                <DropdownLink v-for="(item, index) in dropDownItemsAuth" :key="index" :linkData="item" :selected="$route.name === item.name" :external="item?.external"/>
                <span role="menuitem" tabindex="-1" class="flex justify-between w-full px-4 py-2 text-sm leading-5 text-left text-gray-700 cursor-not-allowed opacity-50" aria-disabled="true">New feature (soon)</span>
            </section>
            <section class="py-1">
                <RouterLink class="text-gray-700 flex justify-between w-full px-4 py-2 text-sm leading-5 text-left" role="menuitem" to="/logout">Log out</RouterLink>
            </section>
        </div>
        <div v-else role="menu" id="user-menu-unauth" class="">
            <section class="">
                <DropdownLink v-for="(item, index) in dropDownItems" :key="index" :linkData="item" :selected="false"/>
            </section>
        </div>
    </div>
</template>