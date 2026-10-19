<script setup lang="ts">
const props = defineProps<{ user: { name: string; extra?: any; avatar?: string } }>();
</script>
<template>
    <div class="data-card pulse-card flex items-center justify-between gap-2">
        <div class="flex items-center gap-3 overflow-hidden">
            <img v-if="props.user?.avatar && props.user.extra !== ''" :src="props.user.avatar" :alt="props.user.name" loading="lazy" class="h-8 w-8 rounded-full object-cover" />

            <div class="overflow-hidden">
                <div class="truncate text-sm font-medium" :title="user.name">
                    {{ user.name }}
                </div>

                <div class="text-foreground-2 truncate text-xs" :title="user.name ?? user.extra">
                    {{ props.user.avatar ? 'Deleted' : user.extra }}
                </div>
            </div>
        </div>

        <div class="text-xl font-bold tabular-nums">
            <slot name="stats"> </slot>
        </div>
    </div>
</template>
