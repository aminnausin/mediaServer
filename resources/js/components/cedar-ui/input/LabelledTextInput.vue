<script setup lang="ts">
import type { LabelledTextInputProps } from '@aminnausin/cedar-ui';

import { computed, useId } from 'vue';
import { FormLabel } from '../form';
import { TextInput } from '.';

const props = defineProps<LabelledTextInputProps>();

const autoId = useId();
const id = computed(() => props.id ?? `text-input-labelled-${autoId}`);

const model = defineModel();
</script>
<template>
    <div class="flex flex-col justify-center gap-2">
        <FormLabel :for="id" :text="text" :subtext="subtext" :id="`${id}-description`" />
        <TextInput v-model="model" :id="id" :placeholder="placeholder" :disabled="disabled" :aria-describedby="`${id}-description`" />
    </div>
</template>
