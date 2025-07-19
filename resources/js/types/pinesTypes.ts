import type { Component } from 'vue';

export type ToastType = 'success' | 'info' | 'warning' | 'danger' | 'default' | 'loading';
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

export interface ToastProps extends Message {
    id: string;
    index: number;
    stack: (...args: unknown[]) => unknown;
    position: ToastPostion;
    toastCount: number;
    expanded: boolean;
    maxVisibleToasts?: number;
    swipeDirections?: SwipeDirection[];
    style?: string;
}

export type SwipeDirection = 'top' | 'right' | 'bottom' | 'left';

export interface Message<T extends Component = Component> {
    id: string;
    title: string;
    description?: string;
    type?: ToastType;
    position?: ToastPostion;
    life?: number;
    html?: string;
    component?: T;
    scale: number;
    zIndex: number;
    offsetY: number;
    positionY?: string;
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

export declare interface ToastToDismiss {
    id: string;
    dismiss: boolean;
}
