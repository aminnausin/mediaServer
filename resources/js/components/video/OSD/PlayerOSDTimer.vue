<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

import { onUnmounted, ref, watch } from 'vue';
import { cn } from '@aminnausin/cedar-ui';

const props = withDefaults(
    defineProps<{
        isTriggered: boolean | number;
        duration?: number;
        hideOnFalse?: boolean;
        enterFrom?: HTMLAttributes['class'];
        enterTo?: HTMLAttributes['class'];
        enterActive?: HTMLAttributes['class'];
        leaveFrom?: HTMLAttributes['class'];
        leaveTo?: HTMLAttributes['class'];
        leaveActive?: HTMLAttributes['class'];
    }>(),
    {
        duration: 3000,
        enterFrom: 'scale-80 opacity-0',
        enterTo: 'scale-100 opacity-100',
        enterActive: 'duration-300 ease-out',
        leaveFrom: 'scale-100 opacity-100',
        leaveTo: 'scale-80 opacity-0',
        leaveActive: 'ease-in',
    },
);

let timeout: ReturnType<typeof setTimeout> | undefined;
const isVisible = ref(false);

const emit = defineEmits(['onHide']);

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
    isVisible.value = false;
    clearTimeout(timeout);
};

watch(() => props.isTriggered, handleChange, { immediate: true });
onUnmounted(() => clearTimeout(timeout));
defineExpose({ handleHide });
</script>

<template>
    <Transition
        :enter-from-class="enterFrom"
        :enter-to-class="enterTo"
        :enter-active-class="enterActive"
        :leave-from-class="leaveFrom"
        :leave-to-class="leaveTo"
        :leave-active-class="leaveActive"
        @after-leave="emit('onHide')"
    >
        <div v-show="isVisible" :class="cn('transition-[scale,opacity]', $attrs.class)">
            <slot> </slot>
        </div>
    </Transition>
</template>
