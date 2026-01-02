<script setup lang="ts">
import type { DropdownMenuItem } from '@aminnausin/cedar-ui';

import { nextTick, onMounted, onUnmounted, ref, useTemplateRef, watch } from 'vue';
import { OnClickOutside } from '@vueuse/components';
import { useAuthStore } from '@/stores/AuthStore';
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';
import { cn } from '@aminnausin/cedar-ui';

import DropdownItem from './DropdownItem.vue';

const { userData } = storeToRefs(useAuthStore());

const props = defineProps<{ dropdownOpen: boolean; dropDownItems: DropdownMenuItem[][]; class?: string }>();
const dropdown = useTemplateRef('dropdown');
const manualPosition = ref(0);
const route = useRoute();

const adjustDropdownPosition = async () => {
    if (!dropdown.value || !dropdown.value.parentElement) return;

    const parentRect = dropdown.value.parentElement.getBoundingClientRect();

    await nextTick();

    if (parentRect.right - 4 - dropdown.value.offsetWidth >= 0) {
        manualPosition.value = 0;
        return;
    }
    manualPosition.value = -parentRect.left + 20;
};

watch(
    () => props.dropdownOpen,
    (value) => {
        if (!value) return;
        adjustDropdownPosition();
    },
);

onMounted(() => {
    window.addEventListener('resize', adjustDropdownPosition);
});

onUnmounted(() => {
    window.removeEventListener('resize', adjustDropdownPosition);
});
</script>

<template>
    <OnClickOutside @trigger="$emit('toggleDropdown', false)">
        <slot name="trigger"></slot>
        <Transition
            enter-active-class="ease-out duration-200"
            enter-from-class="-translate-y-4"
            enter-to-class="translate-y-0"
            leave-active-class="ease-in duration-100"
            leave-from-class="translate-y-0"
            leave-to-class="-translate-y-4 opacity-0"
        >
            <div
                v-show="props.dropdownOpen"
                :class="cn('absolute top-0 z-50 mx-auto mt-12 w-56 max-w-[80vw]', { '-right-1': !manualPosition }, props.class)"
                v-cloak
                id="user-dropdown"
                role="menu"
                :style="manualPosition ? `left: ${manualPosition}px;` : ''"
                ref="dropdown"
            >
                <div class="bg-overlay-t border-overlay-border text-foreground-0 mt-1 rounded-md border p-1 shadow-md backdrop-blur-lg">
                    <div class="px-2 py-1.5 text-sm font-semibold" v-if="userData">{{ userData.email }}</div>
                    <div class="bg-hr -mx-1 my-1 h-px" v-if="userData"></div>
                    <section v-for="(group, groupIndex) in dropDownItems" :key="groupIndex">
                        <div v-if="groupIndex !== 0 && groupIndex !== group.length && group.some((item) => !item.hidden)" class="bg-hr -mx-1 my-1 h-px"></div>
                        <DropdownItem
                            v-for="(item, index) in group.filter((item) => !item.hidden)"
                            :key="index"
                            :linkData="item"
                            :selected="route.name === item.name || route.path === item.name || route.path === item.url"
                            :external="item.external"
                            :disabled="item.disabled ?? false"
                            @click="
                                () => {
                                    $emit('toggleDropdown', false);
                                    if (item.action) item.action();
                                }
                            "
                        >
                            <template #icon>
                                <component :is="item.icon" :class="['size-4', item.iconStrokeWidth ? `[&>*]:stroke-[${item.iconStrokeWidth}]` : '']" />
                            </template>
                        </DropdownItem>
                    </section>
                </div>
            </div>
        </Transition>
    </OnClickOutside>
</template>
