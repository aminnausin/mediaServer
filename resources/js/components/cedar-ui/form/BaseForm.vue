<script setup lang="ts">
defineProps<{ footerClass?: string }>();
</script>
<template>
    <form class="flex flex-col gap-4">
        <slot></slot>

        <template v-if="$slots.footer">
            <footer :class="[`mt-4`, footerClass]">
                <slot name="footer"></slot>
            </footer>
        </template>
    </form>
</template>
