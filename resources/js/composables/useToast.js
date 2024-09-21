import { inject } from 'vue';

export const ToastSymbol = Symbol();

export function useToast() {
    const Toast = inject(ToastSymbol);

    if (!Toast) {
        throw new Error('No Toast provided!');
    }

    return Toast;
}
