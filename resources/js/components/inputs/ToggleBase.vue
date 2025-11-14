<script setup>
const model = defineModel();
const { name } = defineProps(['name']);
// NEEDS A REWORK
</script>

<template>
    <section class="toggle-switch shrink-0 flex flex-col">
        <label class="switch-label" :for="name">
            <input type="checkbox" class="checkbox peer invisible text-white" :name="name" :id="name" v-model="model" :checked="model" />
            <span class="toggleBase peer-checked:bg-white! dark:peer-checked:bg-neutral-50!"></span>
        </label>
    </section>
</template>

<style lang="css" scoped>
.toggle-switch {
    position: relative;
    width: 60px;
    height: 30px;
    --light: #d8dbe0;
    --dark: #27272a;
    --disabled: #d8dbe0;
    --enabled: #27272a;
    --accent: #27272a;
    --link: rgb(27, 129, 112);
    --link-hover: rgb(24, 94, 82);
}

.switch-label {
    position: absolute;
    width: 100%;
    height: 30px;
    background-color: var(--dark);
    border-radius: 15px;
    cursor: pointer;
    border: 2px solid var(--dark);
}

.checkbox {
    position: absolute;
    display: none;
}

.toggle {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 15px;
    -webkit-transition: 0.3s;
    transition: 0.3s;
}

.toggleBase {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 15px;
    -webkit-transition: 0.3s;
    transition: 0.3s;
}

.checkbox:checked ~ .toggle,
.checkbox:checked ~ .toggleBase {
    background-color: var(--light);
}

.toggle::before {
    content: '';
    position: absolute;
    top: 6px;
    left: 6px;
    width: 15px;
    height: 15px;
    border-radius: 50%;
    -webkit-box-shadow: inset 7px -2px 0px 0px var(--light);
    box-shadow: inset 7px -2px 0px 0px var(--light);
    background-color: var(--dark);
    -webkit-transition: 0.3s;
    transition: 0.3s;
}

.toggleBase::before {
    content: '';
    position: absolute;
    top: 6px;
    left: 6px;
    width: 15px;
    height: 15px;
    border-radius: 50%;
    -webkit-box-shadow: inset 15px 0px 0px 0px var(--disabled);
    box-shadow: inset 15px 0px 0px 0px var(--disabled);
    background-color: var(--enabled);
    -webkit-transition: 0.3s;
    transition: 0.3s;
}

.checkbox:checked ~ .toggle::before,
.checkbox:checked ~ .toggleBase::before {
    -webkit-transform: translateX(30px);
    -ms-transform: translateX(30px);
    transform: translateX(30px);
    background-color: var(--accent);
    -webkit-box-shadow: none;
    box-shadow: none;
}
</style>
