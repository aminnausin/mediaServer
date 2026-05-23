import type { HTMLAttributes } from 'vue';
import type { TaskStatus } from '@/types/types';

export function taskStatusClass(status: TaskStatus): HTMLAttributes['class'] {
    switch (status) {
        case 'pending':
            return 'bg-[#e4e4e4] text-gray-900 dark:bg-white';
        case 'processing':
            return 'bg-primary dark:bg-primary-dark';
        case 'cancelled':
        case 'incomplete':
        case 'skipped':
            return 'bg-warning-2';
        case 'failed':
            return 'bg-danger-2 dark:bg-danger-3';
        case 'completed':
            return 'bg-primary-active dark:bg-primary-dark';
    }
}

export function taskProgressBarClass(status: TaskStatus): HTMLAttributes['class'] {
    switch (status) {
        case 'pending':
        case 'processing':
        case 'cancelled':
        case 'incomplete':
        case 'skipped':
            return 'bg-warning-2';
        case 'failed':
            return 'bg-danger-2 ';
        case 'completed':
            return 'bg-primary';
    }
}
