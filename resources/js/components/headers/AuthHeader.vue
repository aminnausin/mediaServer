<script setup lang="ts">
import IconLogo from '@/components/icons/IconLogo.vue';
</script>

<template>
    <div class="flex flex-col items-center gap-1 px-6 pt-8 text-center sm:justify-start sm:pt-0">
        <RouterLink to="/" title="Home" class="hover:text-primary flex items-center gap-2 px-4">
            <IconLogo class="xs:size-8 size-6 lg:size-10" />
            <h1 class="xs:text-3xl text-2xl tracking-wider sm:text-4xl">Media Server</h1>
        </RouterLink>

        <slot name="subtext">
            <p class="text-foreground-2 xs:text-base max-w-60 sm:max-w-none sm:text-lg">
                <slot> </slot>
            </p>
        </slot>
    </div>
</template>
