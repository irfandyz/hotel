<script setup lang="ts">
import { provide, toRef } from 'vue';
import { SelectRoot } from 'radix-vue';
import type { SelectRootEmits, SelectRootProps } from 'radix-vue';

const props = withDefaults(defineProps<SelectRootProps>(), {
    defaultOpen: false,
    open: undefined,
});

const emit = defineEmits<SelectRootEmits>();

provide('SelectRoot', {
    ...toRef(props, 'modelValue'),
    ...toRef(props, 'open'),
    ...toRef(props, 'defaultOpen'),
    ...toRef(props, 'disabled'),
    ...toRef(props, 'required'),
    ...toRef(props, 'name'),
    onValueChange: (value: string) => emit('update:modelValue', value),
    onOpenChange: (open: boolean) => emit('update:open', open),
});
</script>

<template>
    <SelectRoot v-bind="$attrs">
        <slot />
    </SelectRoot>
</template>
