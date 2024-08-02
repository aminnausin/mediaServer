<script setup>
import { onMounted, ref } from 'vue';
import useToastController from '../../composables/useToastController';
import ToastNotification from './ToastNotification.vue';

const $el = ref(null);
const { layout, position } = defineProps(['layout', 'position'])

const toastController = useToastController($el, layout, position);

window.toast = function (message, options = {}) {
    let description = "";
    let type = "default";
    let position = "bottom-left";
    let html = "";
    if (typeof options.description != "undefined")
        description = options.description;
    if (typeof options.type != "undefined") type = options.type;
    if (typeof options.position != "undefined") position = options.position;
    if (typeof options.html != "undefined") html = options?.html;
    return new CustomEvent('toast-show', { detail : { type: type, message: message, description: description, position : position, html: html }})
};

onMounted(() => {
    if (toastController.layout == 'expanded') {
        toastController.expanded = true;
    }
    toastController.stackToasts();
})

const handleSetLayout = (event) => {
    toastController.layout = event.detail.layout;
    if (layout == 'expanded') {
        toastController.expanded = true;
    } else {
        toastController.expanded = false;
    }
    toastController.stackToasts();
}

const handleToastShow = (event) => {
    event.stopPropagation();
    if (event.detail.position) {
        toastController.position = event.detail.position;
    }
    toastController.toasts.unshift({
        id: 'toast-' + Math.random().toString(16).slice(2),
        show: false,
        message: event.detail.message,
        title: event.detail.title,
        description: event.detail.description,
        type: event.detail.type,
        html: event.detail.html
    });
}

</script>
<template>
    <Teleport to="body">
        <ul class="fixed block w-full group z-[99] sm:max-w-xs" id="toastRoot"
            :class="{ 'right-0 top-0 sm:mt-6 sm:mr-6': position == 'top-right', 'left-0 top-0 sm:mt-6 sm:ml-6': position == 'top-left', 'left-1/2 -translate-x-1/2 top-0 sm:mt-6': position == 'top-center', 'right-0 bottom-0 sm:mr-6 sm:mb-6': position == 'bottom-right', 'left-0 bottom-0 sm:ml-6 sm:mb-6': position == 'bottom-left', 'left-1/2 -translate-x-1/2 bottom-0 sm:mb-6': position == 'bottom-center' }"
            v-cloak @mouseenter="toastController.toastsHovered = true;"
            @mouseleave="toastController.toastsHovered = false" ref="$el">
            <template v-for="(toast, index) in toastController.toasts" :key="index">
                <ToastNotification :position="position" :toast="toast" :toastRootData="toastController" :root="$el" />
            </template>
        </ul>
    </Teleport>
    <slot name="app" :handleSetLayout="handleSetLayout" :handleToastShow="handleToastShow">
    </slot>
</template>