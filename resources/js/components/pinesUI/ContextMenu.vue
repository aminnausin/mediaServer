<script setup lang="ts">
import type { ContextMenu } from '@/types/types';

import { nextTick, onMounted, onUnmounted, ref, Teleport, useTemplateRef } from 'vue';
import { useMouseInElement } from '@vueuse/core';
import { OnClickOutside } from '@vueuse/components';

import ContextMenuItem from '@/components/pinesUI/ContextMenuItem.vue';

const props = withDefaults(defineProps<ContextMenu>(), {
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

const contextMenuToggle = async (event: any, override: boolean = true) => {
    if (!override || props.disabled) {
        document.removeEventListener('contextmenu', closeContextMenu);
        contextMenuOpen.value = false;
        return;
    }

    if (!contextMenuOpen.value) {
        document.addEventListener('contextmenu', closeContextMenu);
        contextMenuOpen.value = true;
    }

    event.preventDefault();
    event.stopPropagation();

    contextMenu.value?.$el.classList.add('opacity-0');

    await nextTick(() => {
        calculateContextMenuPosition(event);
        calculateSubMenuPosition(event);
        contextMenu.value?.$el.classList.remove('opacity-0');
    });
};

function calculateContextMenuPosition(clickEvent: MouseEvent) {
    if (!contextMenu.value) return;

    const scrollY = props.scrollContainer === 'body' ? document.body.scrollTop : window.scrollY;
    const scrollX = props.scrollContainer === 'body' ? document.body.scrollLeft : window.scrollX;

    if (window.innerHeight < clickEvent.clientY + contextMenu.value?.$el.offsetHeight) {
        contextMenu.value.$el.style.top = window.innerHeight - contextMenu.value?.$el.offsetHeight + scrollY + 'px';
    } else {
        contextMenu.value.$el.style.top = clickEvent.clientY + scrollY + 'px';
    }
    if (window.innerWidth < clickEvent.clientX + contextMenu.value?.$el.offsetWidth) {
        contextMenu.value.$el.style.left = clickEvent.clientX - contextMenu.value?.$el.offsetWidth + scrollX + 'px';
    } else {
        contextMenu.value.$el.style.left = clickEvent.clientX + 'px';
    }
}

async function calculateSubMenuPosition(clickEvent: MouseEvent) {
    await nextTick();
    const submenus: NodeListOf<HTMLElement> = document.querySelectorAll('[data-submenu]');
    const contextMenuWidth = contextMenu.value?.$el.offsetWidth;

    for (const submenu of submenus) {
        if (window.innerWidth < clickEvent.clientX + contextMenuWidth + submenu.offsetWidth) {
            submenu.classList.add('left-0', '-translate-x-full');
            submenu.classList.remove('right-0', 'translate-x-full');
        } else {
            submenu.classList.remove('left-0', '-translate-x-full');
            submenu.classList.add('right-0', 'translate-x-full');
        }

        const previousElementSiblingRect = submenu.previousElementSibling?.getBoundingClientRect();
        if (previousElementSiblingRect && window.innerHeight < previousElementSiblingRect.top + submenu.offsetHeight) {
            const heightDifference = window.innerHeight - previousElementSiblingRect.top - submenu.offsetHeight;
            submenu.style.top = heightDifference + 'px';
        } else {
            submenu.style.top = '';
        }
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
        enter-from-class="scale-[0.8] opacity-60"
        enter-to-class="scale-100 opacity-100"
        leave-active-class="ease-in-out duration-100"
        leave-from-class="scale-100 opacity-100"
        leave-to-class="scale-[0.1] opacity-50"
    >
        <Teleport :to="teleportTarget" :disabled="teleportDisabled">
            <OnClickOutside
                v-show="contextMenuOpen"
                @trigger="
                    (e: any) => {
                        contextMenuToggle(e, false);
                    }
                "
                ref="contextMenu"
                :class="`absolute z-50 w-48 max-w-[100vw] rounded-md border border-neutral-200/70 bg-white p-1 shadow-xs backdrop-blur-xs transition-all dark:border-neutral-700/10 dark:bg-neutral-800/90 ${style}`"
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
