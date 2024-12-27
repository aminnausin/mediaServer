import type { FormField } from '@/types/types';

import { reactive, watch } from 'vue';

import cloneDeep from 'lodash.clonedeep';
import isEqual from 'lodash.isequal';

interface FormHooks {
    onBefore?: () => Promise<void>;
    onSuccess?: (response: any) => Promise<void>;
    onError?: (error: any) => Promise<void>;
    onFinish?: () => Promise<void>;
}

interface FormState {
    fields: { [key: string]: string };
    field?: any;
    errors: { [key: string]: string };
    dirty: boolean;
    hasErrors: boolean;
    processing: boolean;
    wasSuccessful: boolean;
    recentlySuccessful: boolean;
    submit: (submitFn: (fields: { [key: string]: string }) => Promise<any>, hooks?: FormHooks) => Promise<void>;
    reset: (...fields: string[]) => void;
    clearErrors: (...fields: string[]) => void;
    setErrors: (errors: { [key: string]: string }) => void;
}

export default function useForm(fields: { [key: string]: string }) {
    let defaults = fields;
    let recentlySuccessfulTimeoutId: number;
    const form = reactive<FormState>({
        fields: cloneDeep(fields),
        errors: {},
        dirty: false,
        hasErrors: false,
        processing: false,
        wasSuccessful: false,
        recentlySuccessful: false,

        async submit(submitFn: (fields: { [key: string]: string }) => any, hooks = {}) {
            if (this.processing) return;

            const _hooks = {
                onBefore: async () => {
                    this.processing = true;
                    this.wasSuccessful = false;
                    this.recentlySuccessful = false;
                    clearTimeout(recentlySuccessfulTimeoutId);

                    if (hooks.onBefore) await hooks.onBefore();
                },
                onSuccess: async (response: any) => {
                    this.clearErrors();
                    this.wasSuccessful = true;
                    this.recentlySuccessful = true;

                    recentlySuccessfulTimeoutId = setTimeout(() => {
                        this.recentlySuccessful = false;
                    }, 2000);

                    if (hooks.onSuccess) await hooks.onSuccess(response);

                    defaults = cloneDeep(this.fields);
                },
                onError: async (error: any) => {
                    this.hasErrors = true;

                    if (error?.response?.status === 422 || error?.response?.status === 401) {
                        this.clearErrors();
                        this.setErrors({
                            message: error?.response?.data.message,
                            ...error?.response?.data?.errors,
                        });
                    }

                    if (hooks.onError) await hooks.onError(error);
                },
                onFinish: async () => {
                    this.processing = false;

                    if (hooks.onFinish) await hooks.onFinish();
                },
            };

            await _hooks.onBefore();

            try {
                const response = await submitFn(this.fields);
                await _hooks.onSuccess(response);
            } catch (error) {
                await _hooks.onError(error);
            } finally {
                await _hooks.onFinish();
            }
        },
        reset(...fields) {
            const clonedDefaults = cloneDeep(defaults);

            if (fields.length === 0) {
                this.fields = clonedDefaults;
            } else {
                fields.forEach((field) => {
                    if (clonedDefaults[field] !== undefined) {
                        this.fields[field] = clonedDefaults[field];
                    }
                });
            }
        },
        clearErrors(...fields: string[]) {
            if (fields.length === 0) {
                this.errors = {};
            } else {
                fields.forEach((field) => delete this.errors[field]);
            }

            this.hasErrors = Object.keys(this.errors).length > 0;
        },
        setErrors(errors: { [key: string]: string }) {
            this.errors = { ...this.errors, ...errors };
            this.hasErrors = Object.keys(this.errors).length > 0;
        },
    });

    watch(
        () => form.fields,
        () => {
            form.dirty = !isEqual(form.fields, defaults);
        },
        { immediate: true, deep: true },
    );
    return form;
}
