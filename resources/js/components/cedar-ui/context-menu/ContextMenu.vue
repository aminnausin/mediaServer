<script setup lang="ts">
import type { ContextMenuOptions } from '@aminnausin/cedar-ui';

import { nextTick, onMounted, onUnmounted, ref, Teleport, useTemplateRef } from 'vue';
import { useMouseInElement } from '@vueuse/core';
import { ContextMenuItem } from '@/components/cedar-ui/context-menu';
import { OnClickOutside } from '@vueuse/components';
import { cn } from '@aminnausin/cedar-ui';

const props = withDefaults(defineProps<ContextMenuOptions & { teleportDisabled?: boolean; teleportTarget?: string }>(), {
    positionClasses: 'z-30 left-20 bottom-10',
    style: '',
    disabled: false,
    teleportTarget: 'body',
    teleportDisabled: false,
});

const contextMenuOpen = ref(false);
const contextMenu = useTemplateRef('contextMenu');
const menuStyles = ref<Record<string, string>>({});

const { isOutside } = useMouseInElement(contextMenu);

let lastEvent: MouseEvent | null = null;

const contextMenuToggle = async (event: MouseEvent, override: boolean = true) => {
    if (!override || props.disabled) {
        contextMenuOpen.value = false;
        return;
    }

    // Native context menu only runs if disabled
    event.preventDefault();
    event.stopPropagation();

    // always close first if open, then reopen fresh — matches native explorer behaviour
    if (contextMenuOpen.value) {
        contextMenuOpen.value = false;
        await nextTick();
    }

    if (lastEvent?.clientX === event.clientX && lastEvent?.clientY === event.clientY && lastEvent?.target === event.target) {
        lastEvent = null;
        return;
    }

    contextMenuOpen.value = true;
    lastEvent = event;

    await nextTick();

    calculateContextMenuPosition(event);
    calculateSubMenuPosition(event);
};

function calculateContextMenuPosition(event: MouseEvent) {
    const el = contextMenu.value?.$el as HTMLElement | undefined;
    if (!el) return;

    const scrollY = props.scrollContainer === 'body' ? document.body.scrollTop : window.scrollY;
    const scrollX = props.scrollContainer === 'body' ? document.body.scrollLeft : window.scrollX;

    const overflowsBottom = event.clientY + el.offsetHeight > window.innerHeight;
    const overflowsRight = event.clientX + el.offsetWidth > window.innerWidth;

    el.style.top = Math.max(overflowsBottom ? window.innerHeight - el.offsetHeight : event.clientY, 0) + scrollY + 'px';
    el.style.left = Math.max((overflowsRight ? event.clientX - el.offsetWidth : event.clientX) + scrollX, 0) + 'px';
}

async function calculateSubMenuPosition(event: MouseEvent) {
    await nextTick();

    const el = contextMenu.value?.$el as HTMLElement | undefined;
    if (!el) return;

    const submenus = document.querySelectorAll<HTMLElement>('[data-submenu]');
    const menuRight = event.clientX + el.offsetWidth;

    for (const submenu of submenus) {
        const overflowsRight = menuRight + submenu.offsetWidth > window.innerWidth;
        const overflowsLeft = el.offsetLeft - submenu.offsetWidth < 0;

        const floating = !(overflowsLeft && overflowsRight);
        submenu.dataset.floating = String(floating);

        submenu.classList.toggle('left-0', overflowsRight);
        submenu.classList.toggle('-translate-x-full', overflowsRight && !overflowsLeft);
        submenu.classList.toggle('right-0', !overflowsRight);
        submenu.classList.toggle('translate-x-full', !overflowsRight);
        submenu.classList.toggle('floating-menu', floating);

        const triggerRect = submenu.previousElementSibling?.getBoundingClientRect();
        const overflowsBottom = triggerRect && triggerRect.top + submenu.offsetHeight > window.innerHeight;

        if (overflowsLeft && overflowsRight) {
            submenu.style.top = '0px';
            submenu.style.left = '0px';
            return;
        }

        submenu.style.top = overflowsBottom ? `${window.innerHeight - triggerRect!.top - submenu.offsetHeight}px` : '';
    }
}

const closeContextMenu = (e: any) => {
    if (contextMenuOpen.value && isOutside.value) {
        e.preventDefault();
        document.removeEventListener('contextmenu', closeContextMenu);
        contextMenuOpen.value = false;
    }
};

onMounted(() => {
    window.addEventListener('resize', closeContextMenu);
});

onUnmounted(() => {
    window.removeEventListener('resize', closeContextMenu);
});

defineExpose({ contextMenuToggle, contextMenuOpen });
</script>
<template>
    <Transition
        enter-active-class="ease-out duration-150"
        enter-from-class="scale-80 opacity-0"
        enter-to-class="scale-100 opacity-100"
        leave-active-class="ease-in-out duration-100"
        leave-from-class="scale-100 opacity-100"
        leave-to-class="scale-60 opacity-0"
    >
        <Teleport :to="teleportTarget" :disabled="teleportDisabled">
            <OnClickOutside
                v-if="contextMenuOpen"
                @trigger="
                    (e: MouseEvent) => {
                        contextMenuToggle(e, false);
                    }
                "
                ref="contextMenu"
                :class="
                    cn(
                        'absolute z-50 w-48 max-w-[100vw]',
                        'rounded-md border p-1 shadow-xs backdrop-blur-xs',
                        'bg-overlay-2-t border-overlay-border/10 pointer-events-auto',
                        'origin-top-left text-xs transition-[opacity,scale]',
                        style,
                    )
                "
                :style="menuStyles"
                v-cloak
            >
                <slot name="content">
                    <ContextMenuItem
                        v-for="(item, index) in (items ?? []).filter((item) => !item.hidden)"
                        v-bind="item"
                        :key="index"
                        :class="itemStyle"
                        @click="
                            (e: any) => {
                                contextMenuToggle(e, false);
                            }
                        "
                    />
                </slot>
            </OnClickOutside>
        </Teleport>
    </Transition>
</template>
