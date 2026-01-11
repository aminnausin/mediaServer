<script setup lang="ts">
import { useUserProfileById } from '@/service/users/useUsers';
import { toFormattedDate } from '@/service/util';

const props = defineProps<{ editor_id: number; updated_at: string }>();

const { data: editor, isLoading } = useUserProfileById(props.editor_id);
</script>
<template>
    <div v-if="isLoading" class="suspense-rounded bg-surface-2 h-5 w-32"></div>
    <template v-else>
        Last edited by
        <a title="Editor profile" target="_blank" :href="`/profile/${editor?.name ?? editor_id}`" class="hover:text-primary dark:hover:text-primary-muted">
            @{{ editor?.name ?? editor_id }}
        </a>
        at
        {{ toFormattedDate(new Date(updated_at)) }}
    </template>
</template>
