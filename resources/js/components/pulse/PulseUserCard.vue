<script setup lang="ts">
const props = defineProps<{ user: { name: string; extra?: any; avatar?: string } }>();
</script>
<template>
    <div class="flex items-center justify-between p-4 gap-2 rounded-md dark:bg-primary-dark-800 bg-primary-900 ring-1 ring-gray-900/5 shadow-xs ring-inset">
        <div class="flex items-center gap-3 overflow-hidden">
            <img v-if="props.user?.avatar && props.user.extra !== ''" :src="props.user.avatar" :alt="props.user.name" loading="lazy" class="rounded-full w-8 h-8 object-cover" />

            <div class="overflow-hidden">
                <div class="text-sm text-gray-900 dark:text-gray-100 font-medium truncate" :title="user.name">
                    {{ user.name }}
                </div>

                <div class="text-xs text-gray-500 dark:text-gray-400 truncate" :title="user.name ?? user.extra">
                    {{ props.user.avatar ? 'Deleted' : user.extra }}
                </div>
            </div>
        </div>

        <div class="text-xl text-gray-900 dark:text-gray-100 font-bold tabular-nums">
            <slot name="stats"> </slot>
        </div>
    </div>
</template>
