<script setup lang="ts">
import { CopyToClipboard } from '@/components/cedar-ui/clipboard';
import { useModalStore } from '@/stores/ModalStore';
import { BaseModal } from '@/components/cedar-ui/modal';

const modalStore = useModalStore();
</script>

<template>
    <BaseModal>
        <template #title>{{ modalStore.props.title }}</template>
        <template #description> Copy link to clipboard to share it.</template>
        <CopyToClipboard :text="modalStore.props.shareLink" />
    </BaseModal>
</template>
