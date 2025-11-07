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
                :class="`absolute z-50 w-48 max-w-[100vw] rounded-md border border-neutral-200/70 bg-white p-1 shadow-sm backdrop-blur-sm transition-all dark:border-neutral-700/10 dark:bg-neutral-800/90 ${style}`"
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
                    <!-- Defaults (Not needed) -->
                    <span v-if="!items">
                        <div
                            @click="(e: any) => contextMenuToggle(e, false)"
                            class="group relative flex cursor-default select-none items-center rounded px-2 py-1.5 pl-8 outline-none hover:bg-neutral-100 data-[disabled]:pointer-events-none data-[disabled]:opacity-50"
                        >
                            <span>Edit</span>
                            <span class="ml-auto text-xs tracking-widest text-neutral-400 group-hover:text-neutral-600">⌘[</span>
                        </div>
                        <div
                            @click="(e: any) => contextMenuToggle(e, false)"
                            class="group relative flex cursor-default select-none items-center rounded px-2 py-1.5 pl-8 outline-none hover:bg-purple-600 hover:text-white data-[disabled]:pointer-events-none data-[disabled]:opacity-50"
                        >
                            <svg class="absolute left-2 -mt-px h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75"
                                />
                            </svg>
                            <span>Duplicate</span>
                        </div>
                        <div
                            @click="(e: any) => contextMenuToggle(e, false)"
                            class="group relative flex cursor-default select-none items-center rounded px-2 py-1.5 pl-8 outline-none hover:bg-purple-600 hover:text-white data-[disabled]:pointer-events-none data-[disabled]:opacity-50"
                        >
                            <svg class="absolute left-2 -mt-px h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                            <span>Quick Look</span>
                        </div>
                        <ContextMenuItem text="Edit" class="hover:bg-purple-600 hover:text-white" />
                        <ContextMenuItem text="Share" />
                    </span>
                </slot>
            </OnClickOutside>
        </Teleport>
    </Transition>
</template>
