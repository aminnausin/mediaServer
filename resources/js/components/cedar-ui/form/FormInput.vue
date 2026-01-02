<script setup lang="ts">
import type { FormField } from '@aminnausin/cedar-ui';

import { InputShell } from '../input';
import { cn } from '@aminnausin/cedar-ui';

defineProps<{ field: FormField }>();
const model = defineModel();
</script>

<template>
    <InputShell>
        <template #input="{ class: inputClass }">
            <input
                v-model="model"
                :type="field.type"
                :id="field.name"
                :name="field.name"
                :title="field.text ?? field.name"
                :required="field.required"
                :disabled="field.disabled"
                :placeholder="field.placeholder"
                :autocomplete="field.autocomplete"
                :aria-autocomplete="field.ariaAutocomplete ?? (field.autocomplete ? 'list' : 'none')"
                :class="cn(inputClass, 'mt-1')"
                :min="field.min"
                :max="field.max"
            />
        </template>
    </InputShell>
</template>
