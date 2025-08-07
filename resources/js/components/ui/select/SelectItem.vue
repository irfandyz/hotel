<script setup lang="ts">
import { inject } from 'vue';
import { SelectItem as SelectItemPrimitive } from 'radix-vue';
import { cn } from '@/lib/utils';

const selectRoot = inject('SelectRoot');

const props = withDefaults(defineProps<{
    class?: string;
    value: string;
    disabled?: boolean;
}>(), {
    class: '',
    disabled: false,
});
</script>

<template>
    <SelectItemPrimitive
        :class="cn(
            'relative flex w-full cursor-default select-none items-center rounded-sm py-1.5 pl-8 pr-2 text-sm outline-none focus:bg-accent focus:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50',
            props.class
        )"
        :value="props.value"
        :disabled="props.disabled"
    >
        <slot />
    </SelectItemPrimitive>
</template>
