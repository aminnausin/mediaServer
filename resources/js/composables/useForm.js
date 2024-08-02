import cloneDeep from "lodash.clonedeep";
import isEqual from "lodash.isequal";
import { reactive, watch } from "vue";

export default function useForm(fields) {
    let defaults = fields;
    let recentlySuccessfulTimeoutId;
    const form = reactive({
        fields: cloneDeep(fields),
        errors: {},
        dirty: false,
        hasErrors: false,
        processing: false,
        wasSuccessful: false,
        recentlySuccessful: false,

        async submit(submitFn, hooks = {}) {
            if(this.processing) return;

            const _hooks = {
                onBefore: async () => {
                    this.processing = true;
                    this.wasSuccessful = false;
                    this.recentlySuccessful = false;
                    clearTimeout(recentlySuccessfulTimeoutId);

                    if(hooks.onBefore) await hooks.onBefore();
                },
                onSuccess: async (response) => {
                    this.clearErrors();
                    this.wasSuccessful = true;
                    this.recentlySuccessful = true;


                    recentlySuccessfulTimeoutId = setTimeout(() => {
                        this.recentlySuccessful = false;
                    }, 2000);

                    if(hooks.onSuccess) await hooks.onSuccess(response);


                    defaults = cloneDeep(this.fields);
                },
                onError: async (error) => {
                    console.log(error?.response?.data?.message ?? error);
                    this.hasErrors = true;

                    if(error?.response?.status === 422 || error?.response?.status === 401){
                        this.clearErrors();
                        this.setErrors({message: error?.response?.data.message, ...error?.response?.data?.errors});
                    }

                    if(hooks.onError) await hooks.onError(error);
                },
                onFinish: async () => {
                    this.processing = false;

                    if(hooks.onFinish) await hooks.onFinish();
                },
            }

            await _hooks.onBefore();

            try {
                const response = await(submitFn(this.fields));
                await _hooks.onSuccess(response);
            } catch(error){
                await _hooks.onError(error);
            } finally {
                await _hooks.onFinish();
            }
            
        },
        reset(...fields) {
            const clonedDefaults = cloneDeep(defaults);
            
            if(fields.length === 0){
                this.field = cloneDeep;
            } else {
                fields.forEach((field) => {
                    if(clonedDefaults[field] !== undefined){
                        this.fields[field] = clonedDefaults[field];
                    }
                });
            }
        },
        clearErrors(...fields) {
            if(fields.length === 0){
                this.errors = {};
            } else {
                fields.forEach((field) => delete this.errors[field]);
            }

            this.hasErrors = Object.keys(this.errors).length > 0;
        },
        setErrors(errors) {
            this.errors = {...this.errors, ...errors}
            console.log(errors);
            this.hasErrors = Object.keys(this.errors).length > 0;
        },
    })

    watch(
        () => form.fields,
        () => {
            form.dirty = !isEqual(form.fields, defaults);
        },
        { immediate: true, deep: true }
    )
    return form;
}