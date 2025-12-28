<script setup lang="ts">
import type { FormFieldValue, FormNumberFieldProps } from '@aminnausin/cedar-ui';

import { LucideChevronDown, LucideChevronUp } from '../icons';
import { ButtonIcon } from '../button';
import { InputShell } from '../input';

const props = withDefaults(defineProps<FormNumberFieldProps>(), { increment: 1 });
const model = defineModel<FormFieldValue>();

const normalise = (): number | null => {
    if (typeof model.value !== 'number') {
        model.value = 0;
    }
    return model.value;
};

const clampValue = (value: number) => {
    const { min, max } = props.field;

    if (min != undefined && value < min) return min;
    if (max != undefined && value > max) return max;

    return value;
};

const step = (delta: 1 | -1) => {
    const current = normalise();
    if (current == null) return;

    const next = current + delta * props.increment;
    model.value = clampValue(next);
};

const clamp = () => {
    if (typeof model.value !== 'number') return;
    model.value = clampValue(model.value);
};
</script>

<template>
    <span class="relative mt-1 inline-flex w-full text-sm">
        <InputShell>
            <template #input="{ class: inputClass }">
                <input
                    @change="clamp"
                    @blur="clamp"
                    v-model="model"
                    type="number"
                    :id="field.name"
                    :name="field.name"
                    :title="field.text ?? field.name"
                    :required="field.required"
                    :disabled="field.disabled"
                    :placeholder="field.placeholder"
                    :aria-autocomplete="field.autocomplete ? 'list' : 'none'"
                    :class="['pe-12', inputClass]"
                    :min="field.min"
                    :max="field.max"
                />
            </template>
        </InputShell>

        <span class="absolute top-0 right-0 flex h-full w-12 flex-col">
            <ButtonIcon
                @click.prevent.stop="step(1)"
                class="focus:ring-primary hover:bg-overlay-border/50 h-1/2 rounded-none rounded-tr-md ring-inset focus:ring-2"
                :title="`Increment ${field.name}`"
                :variant="'ghost'"
            >
                <LucideChevronUp width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="size-4" />
            </ButtonIcon>
            <ButtonIcon
                @click.prevent.stop="step(-1)"
                class="focus:ring-primary hover:bg-overlay-border/50 h-1/2 rounded-none rounded-br-md ring-inset focus:ring-2"
                :title="`Decrement ${field.name}`"
                :variant="'ghost'"
            >
                <LucideChevronDown width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="size-4" />
            </ButtonIcon>
        </span>
    </span>
</template>
