<script setup lang="ts">
import { ref, watch } from 'vue';
import { cn } from '@aminnausin/cedar-ui';

const props = withDefaults(defineProps<{ isTriggered: boolean; duration?: number; hideOnFalse?: boolean }>(), {
    duration: 3000,
});

let timeout: ReturnType<typeof setTimeout> | undefined;
const isVisible = ref(false);

const handleChange = () => {
    if (!props.isTriggered) {
        if (props.hideOnFalse) handleHide();
        return;
    }

    clearTimeout(timeout);
    if (!isVisible.value) isVisible.value = true;
    timeout = setTimeout(handleHide, props.duration);
};

const handleHide = () => {
    clearTimeout(timeout);
    isVisible.value = false;
    timeout = undefined;
};

watch(() => props.isTriggered, handleChange, { immediate: true });
defineExpose({ handleHide });
</script>

<template>
    <Transition
        enter-from-class="scale-80 opacity-0"
        enter-to-class="scale-100 opacity-100"
        enter-active-class="duration-300 ease-out"
        leave-from-class="scale-100 opacity-100"
        leave-to-class="scale-80 opacity-0"
        leave-active-class="ease-in"
    >
        <div v-show="isVisible" :class="cn('transition-[scale,opacity]', $slots.content ? $attrs.class : '')">
            <slot> </slot>
        </div>
    </Transition>
</template>
