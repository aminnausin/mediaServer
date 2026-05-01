<script setup lang="ts">
import { ButtonText } from '@/components/cedar-ui/button';
defineProps<{ isVisible: boolean }>();
</script>

<template>
    <Transition
        enter-from-class="opacity-0 "
        enter-to-class="opacity-100"
        enter-active-class="ease-out"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
        leave-active-class="ease-in"
    >
        <ButtonText v-cloak v-show="isVisible" :class="['pointer-events-auto w-fit justify-normal bg-neutral-900/60 text-white ring-transparent backdrop-blur-xs select-none']">
            <template #default>
                <slot> Overlay Button </slot>
            </template>
            <template #icon>
                <slot name="icon"> </slot>
            </template>
        </ButtonText>
    </Transition>
</template>
