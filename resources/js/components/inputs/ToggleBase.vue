<script setup>
const model = defineModel();
const { name } = defineProps(['name']);
</script>

<template>
    <section class="toggle-switch shrink-0 flex flex-col">
        <label class="switch-label" :for="name">
            <input type="checkbox" class="checkbox invisible text-white" :name="name" v-model="model" v-bind:checked="model" />
            <span class="sliderBase"></span>
        </label>
    </section>
</template>
