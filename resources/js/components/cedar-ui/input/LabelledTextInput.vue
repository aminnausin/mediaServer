<script setup lang="ts">
import type { LabelledTextInputProps } from '@aminnausin/cedar-ui';

import { FormLabel } from '../form';
import { TextInput } from '.';

withDefaults(defineProps<LabelledTextInputProps>(), {
    id: 'text-input-labelled-' + Math.floor(Math.random() * 10000),
});

const model = defineModel();
</script>
<template>
    <div class="flex flex-col justify-center gap-2">
        <FormLabel :for="id" :text="text" :subtext="subtext" :id="`${id}-description`" />
        <TextInput v-model="model" :id="id" :placeholder="placeholder" :disabled="disabled" :aria-describedby="`${id}-description`" />
    </div>
</template>
