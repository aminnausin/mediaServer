import type { Component } from 'vue';

export type ToastType = 'success' | 'info' | 'warning' | 'danger' | 'default';
export type ToastPostion = 'top-right' | 'top-left' | 'top-center' | 'bottom-right' | 'bottom-left' | 'bottom-center';
export type ToastLayout = 'default' | 'expanded';

export interface ToastIcons {
    success?: Component;
    info?: Component;
    warning?: Component;
    error?: Component;
    loading?: Component;
    close?: Component;
}

export interface ToastControllerProps {
    layout?: 'default' | 'expanded';
    position?: ToastPostion;
    defaultLife?: number;
    viewportOffset?: string | number;
    mobileViewportOffset?: string | number;
    paddingBetweenToasts?: number; // gap
    maxVisibleToasts?: number;
    closeButton?: boolean;
    icons?: ToastIcons;
}

export interface ToastProps {
    id: string;
    stack: Function;
    position: string;
    toastCount: number;

    // idx: number;

    title?: string;
    description?: string;
    life?: number;
    type?: string;
    style?: string;
    html?: string;
}

export interface Message<T extends Component = Component> {
    type: ToastType;
    position: ToastPostion;
    life: number;
    id: string;
    title: string;
    description?: string;
    html?: string;
    component?: T;
}

export interface ExternalToast {
    title: string;
    options: ToastOptions;
}

export interface ToastOptions {
    id?: string;
    description?: string;
    type?: ToastType;
    position?: ToastPostion;
    life?: number;
}

export interface ToastT {
    id: string;
}

export declare const toast: (title: string, options?: ToastOptions) => string | number;
