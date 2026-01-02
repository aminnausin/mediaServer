import { computed } from 'vue';

export function useDateFieldModel(form: { fields: any }, fieldName: string) {
    return computed<string | null | undefined>({
        get() {
            const value = form.fields[fieldName];
            return typeof value === 'string' || value === null ? value : undefined;
        },
        set(value) {
            form.fields[fieldName] = value;
        },
    });
}
