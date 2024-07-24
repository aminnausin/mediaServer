<script setup>
    const {action, label, colour} = defineProps({action, label, colour});
</script>
<template>
    <button :id="'btn-nav-' + label" @click="action" :class="`hidden justify-center items-center shrink-0 h-8 w-8 rounded-lg shadow-lg bg-${colour}-300 hover:bg-${colour}-200`" :aria-label="label">
        <svg xmlns="https://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" height="24" width="24" class="fill-slate-900">
            <path fill-rule="evenodd" d="M3 6.75A.75.75 0 0 1 3.75 6h16.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 6.75ZM3 12a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 12Zm0 5.25a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 0 1.5H3.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
        </svg>
    </button>
</template>