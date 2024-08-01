<script setup lang="ts">
import DropdownMenu from '../components/pinesUI/DropdownMenu.vue';
import LayoutBase from '../components/layout/LayoutBase.vue';
</script>

<template>
    <LayoutBase>
        <template v-slot:content>
            <section class="min-h-[80vh]">
                <DropdownMenu />
            </section>
        </template>
    </LayoutBase>
</template>