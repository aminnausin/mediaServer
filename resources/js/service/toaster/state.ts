import type { ExternalToast, Message, ToastOptions, ToastType } from '@/types/pinesTypes';
import type { Component } from 'vue';

function UniqueComponentId(prefix = 'pv_id_') {
    return prefix + Math.random().toString(16).slice(2);
}
let toastsCounter = 0;

class Observer {
    subscribers: Array<(toast: Message | { id: string }) => void>;
    toasts: Array<Message>;

    constructor() {
        this.subscribers = [];
        this.toasts = [];
    }

    // We use arrow functions to maintain the correct `this` reference
    subscribe = (subscriber: (toast: Message | { id: string }) => void) => {
        this.subscribers.push(subscriber as any);

        return () => {
            const index = this.subscribers.indexOf(subscriber as any);
            this.subscribers.splice(index, 1);
        };
    };

    publish = (data: Message) => {
        this.subscribers.forEach((subscriber) => subscriber(data));
    };

    addToast = (data: Message) => {
        this.publish(data);
        this.toasts = [...this.toasts, data];
    };

    create = (
        data: ExternalToast & {
            message?: string;
            type?: ToastType;
            life?: number;
        },
    ) => {
        const { message, ...rest } = data;
        const id = data.options.id ?? UniqueComponentId('toast_');
        const alreadyExists = this.toasts.find((toast) => {
            return toast.id === id;
        });

        if (alreadyExists) {
            this.toasts = this.toasts.map((toast) => {
                if (toast.id === id) {
                    this.publish({ ...toast, ...data, id, title: message ?? '' });
                    return {
                        ...toast,
                        ...data,
                        id,
                        title: message ?? '',
                    };
                }

                return toast;
            });
        } else {
            this.addToast({
                ...rest,
                title: message ?? '',
                id,
                position: 'bottom-right',
                life: 3000,
                type: 'success',
            });
        }

        return id;
    };
    dismiss = (id?: string) => {
        if (id) {
            this.subscribers.forEach((subscriber) => subscriber({ id }));
        }
        this.toasts.forEach((toast) => {
            this.subscribers.forEach((subscriber) => subscriber({ id: toast.id }));
        });
        return id;
    };

    add = (message: string, options?: ToastOptions) => {
        return this.create({ title: message, options: options ?? {} });
    };
}

export const ToastState = new Observer();

function toastFunction(message: string, options?: ToastOptions) {
    const id = UniqueComponentId('toast_');

    ToastState.create({ title: message, options: { type: 'default', ...options, id: id } });

    return id;
}

const basicToast = toastFunction;

export const toast = Object.assign(basicToast, {
    add: ToastState.add,
    dismiss: ToastState.dismiss,
});
